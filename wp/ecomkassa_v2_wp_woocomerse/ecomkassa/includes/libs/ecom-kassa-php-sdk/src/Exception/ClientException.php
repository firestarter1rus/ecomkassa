<?php

/**
 * This file is part of the ecom/kassa-sdk library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecom\KassaSdk\Exception;

class ClientException extends \RuntimeException implements SdkException {}

class ClientExceptionError extends \RuntimeException implements SdkException {}

class ClientExceptionErrorIncomingMissingToken extends \RuntimeException implements SdkException {}

class ClientExceptionErrorIncomingValidationException extends \RuntimeException implements SdkException {}

class ClientExceptionErrorAuthWrongUserOrPassword extends \RuntimeException implements SdkException {}
