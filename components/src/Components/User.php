<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Components;

use League\Uri\Interfaces;

/**
 * Value object representing a URI user component.
 *
 * @package League.uri
 * @since  1.0.0
 */
class User extends AbstractComponent implements Interfaces\Components\User
{
    protected static $invalidCharactersRegex = ',[/:@?#],';
}
