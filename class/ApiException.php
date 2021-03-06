<?php
/**
 * This class implements specifc exception to REST-Like API bus.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage api
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Form, Api, ApiAuth, ApiEntity, ApiList
 * @link http://www.titanframework.com/docs/api/
 */
class ApiException extends Exception
{
	// 1XX INFORMATIONAL CODES
	const CONTINUE_STATUS_CODE = 100;
	const SWITCHING_PROTOCOLS = 101;
	const PROCESSING = 102;

	// 2XX SUCCESS CODES
	const OK = 200;
	const CREATED = 201;
	const ACCEPTED = 202;
	const NON_AUTHORITATIVE_INFORMATION = 203;
	const NO_CONTENT = 204;
	const RESET_CONTENT = 205;
	const PARTIAL_CONTENT = 206;
	const MULTISTATUS = 207;
	const ALREADY_REPORTED = 208;

	// 3XX REDIRECTION CODES
	const MULTIPLE_CHOICES = 300;
	const MOVED_PERMANENTLY = 301;
	const FOUND = 302;
	const SEE_OTHER = 303;
	const NOT_MODIFIED = 304;
	const USE_PROXY = 305;
	const SWITCH_PROXY = 306; # Deprecated
	const TEMPORARY_REDIRECT = 307;

	// 4XX CLIENT ERROR
	const BAD_REQUEST = 400;
	const UNAUTHORIZED = 401;
	const PAYMENT_REQUIRED = 402;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const NOT_ACCEPTABLE = 406;
	const PROXY_AUTHENTICATION_REQUIRED = 407;
	const REQUEST_TIME_OUT = 408;
	const CONFLICT = 409;
	const GONE = 410;
	const LENGTH_REQUIRED = 411;
	const PRECONDITION_FAILED = 412;
	const REQUEST_ENTITY_TOO_LARGE = 413;
	const REQUEST_URI_TOO_LARGE = 414;
	const UNSUPPORTED_MEDIA_TYPE = 415;
	const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const EXPECTATION_FAILED = 417;
	const I_AM_A_TEAPOT = 418;
	const UNPROCESSABLE_ENTITY = 422;
	const LOCKED = 423;
	const FAILED_DEPENDENCY = 424;
	const UNORDERED_COLLECTION = 425;
	const UPGRADE_REQUIRED = 426;
	const PRECONDITION_REQUIRED = 428;
	const TOO_MANY_REQUESTS = 429;
	const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

	// 5XX SERVER ERROR
	const INTERNAL_SERVER_ERROR = 500;
	const NOT_IMPLEMENTED = 501;
	const BAD_GATEWAY = 502;
	const SERVICE_UNAVAILABLE = 503;
	const GATEWAY_TIME_OUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;
	const VARIANT_ALSO_NEGOTIATES = 506;
	const INSUFFICIENT_STORAGE = 507;
	const LOOP_DETECTED = 508;
	const NETWORK_AUTHENTICATION_REQUIRED = 511;

	public static $status = array (
		// INFORMATIONAL CODES
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',

		// SUCCESS CODES
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-status',
		208 => 'Already Reported',

		// REDIRECTION CODES
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy', // Deprecated
		307 => 'Temporary Redirect',

		// CLIENT ERROR
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested range not satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',

		// SERVER ERROR
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		511 => 'Network Authentication Required'
	);

	const ERROR_REQUEST_TIMESTAMP = 'ERROR_REQUEST_TIMESTAMP';
	const ERROR_INVALID_PARAMETER = 'ERROR_INVALID_PARAMETER';
	const ERROR_APP_AUTH = 'ERROR_APP_AUTH';
	const ERROR_CLIENT_AUTH = 'ERROR_CLIENT_AUTH';
	const ERROR_USER_AUTH = 'ERROR_USER_AUTH';
	const ERROR_RESOURCE_MISSING = 'ERROR_RESOURCE_MISSING';
	const ERROR_SYSTEM = 'ERROR_SYSTEM';
	const ERROR_DB = 'ERROR_DB';
	const ERROR_NOT_FOUND = 'ERROR_NOT_FOUND';
	const ERROR_GENERIC = 'ERROR_GENERIC';

	private $titanErrorCode = '';
	private $titanTechnical = '';

	public function __construct ($message, $error, $http = self::INTERNAL_SERVER_ERROR, $technical = '')
	{
		parent::__construct ($message, $http);

		$this->titanErrorCode = $error;
		$this->titanTechnical = $technical;
	}

	public function getTitanErrorCode ()
	{
		return $this->titanErrorCode;
	}

	public function getTitanTechnical ()
	{
		return $this->titanTechnical;
	}
}
