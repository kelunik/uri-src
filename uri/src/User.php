<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Uri;

/**
 * Value object representing a URL user component.
 *
 * @package League.url
 * @since  1.0.0
 */
class User extends AbstractComponent implements Interfaces\User
{
    protected static $invalidCharactersRegex = ",[/:@],";
}
