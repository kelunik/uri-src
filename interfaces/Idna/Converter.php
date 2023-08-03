<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Uri\Idna;

use League\Uri\Exceptions\SyntaxError;
use function defined;
use function function_exists;
use function idn_to_ascii;
use function idn_to_utf8;
use function rawurldecode;
use const INTL_IDNA_VARIANT_UTS46;

/**
 * @see https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/uidna_8h.html
 */
final class Converter
{
    private const REGEXP_IDNA_PATTERN = '/[^\x20-\x7f]/';
    private const MAX_DOMAIN_LENGTH = 253;
    private const MAX_LABEL_LENGTH = 63;

    /**
     * General registered name regular expression.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2.2
     * @see https://regex101.com/r/fptU8V/1
     */
    private const REGEXP_REGISTERED_NAME = '/
        (?(DEFINE)
            (?<unreserved>[a-z0-9_~\-])   # . is missing as it is used to separate labels
            (?<sub_delims>[!$&\'()*+,;=])
            (?<encoded>%[A-F0-9]{2})
            (?<reg_name>(?:(?&unreserved)|(?&sub_delims)|(?&encoded))*)
        )
            ^(?:(?&reg_name)\.)*(?&reg_name)\.?$
        /ix';

    /**
     * @codeCoverageIgnore
     */
    private static function supportsIdna(): void
    {
        static $idnSupport;
        if (null === $idnSupport) {
            $idnSupport = function_exists('\idn_to_ascii') && defined('\INTL_IDNA_VARIANT_UTS46');
        }

        if (!$idnSupport) {
            throw new MissingSupport('Support for IDN host requires the `intl` extension for best performance or run "composer require symfony/polyfill-intl-idn".');
        }
    }

    /**
     * Converts the input to its IDNA ASCII form.
     *
     * This method returns the string converted to IDN ASCII form
     *
     * @throws SyntaxError if the string can not be converted to ASCII using IDN UTS46 algorithm
     */
    public static function toAscii(string $domain, Option|int $options = null): Result
    {
        $domain = rawurldecode($domain);

        if (1 === preg_match(self::REGEXP_IDNA_PATTERN, $domain)) {
            self::supportsIdna();
            idn_to_ascii(
                $domain,
                Option::new($options ?? Option::forIDNA2008Ascii())->toBytes(),
                INTL_IDNA_VARIANT_UTS46,
                $idnaInfo
            );

            if ([] === $idnaInfo) {
                return Result::fromIntl([
                    'result' => strtolower($domain),
                    'isTransitionalDifferent' => false,
                    'errors' => self::validateDomainAndLabelLength($domain),
                ]);
            }

            /* @var array{errors: int, isTransitionalDifferent: bool, result: string} $idnaInfo */
            return Result::fromIntl($idnaInfo);
        }

        $error = Error::NONE->value;
        if (1 !== preg_match(self::REGEXP_REGISTERED_NAME, $domain)) {
            $error |= Error::DISALLOWED->value;
        }

        return Result::fromIntl([
            'result' => strtolower($domain),
            'isTransitionalDifferent' => false,
            'errors' => self::validateDomainAndLabelLength($domain) | $error,
        ]);
    }

    /**
     * Converts the input to its IDNA UNICODE form.
     *
     * This method returns the string converted to IDN UNICODE form
     *
     * @throws SyntaxError if the string can not be converted to UNICODE using IDN UTS46 algorithm
     */
    public static function toUnicode(string $domain, Option|int $options = null): Result
    {
        $domain = rawurldecode($domain);

        if (false === stripos($domain, 'xn--')) {
            return Result::fromIntl(['result' => $domain, 'isTransitionalDifferent' => false, 'errors' => Error::NONE->value]);
        }

        self::supportsIdna();
        idn_to_utf8(
            $domain,
            Option::new($options ?? Option::forIDNA2008Unicode())->toBytes(),
            INTL_IDNA_VARIANT_UTS46,
            $idnaInfo
        );

        if ([] === $idnaInfo) {
            throw ConversionFailed::dueToInvalidHost($domain);
        }

        return Result::fromIntl($idnaInfo);
    }

    /**
     * Adapted from https://github.com/TRowbotham/idna.
     *
     * @see https://github.com/TRowbotham/idna/blob/master/src/Idna.php#L236
     */
    private static function validateDomainAndLabelLength(string $domain): int
    {
        $error = Error::NONE->value;
        $labels = explode('.', $domain);
        $maxDomainSize = self::MAX_DOMAIN_LENGTH;
        $length = count($labels);

        // If the last label is empty and it is not the first label, then it is the root label.
        // Increase the max size by 1, making it 254, to account for the root label's "."
        // delimiter. This also means we don't need to check the last label's length for being too
        // long.
        if ($length > 1 && '' === $labels[$length - 1]) {
            ++$maxDomainSize;
            array_pop($labels);
        }

        if (strlen($domain) > $maxDomainSize) {
            $error |= Error::DOMAIN_NAME_TOO_LONG->value;
        }

        foreach ($labels as $label) {
            if (strlen($label) > self::MAX_LABEL_LENGTH) {
                $error |= Error::LABEL_TOO_LONG->value;

                break;
            }
        }

        return $error;
    }
}