<?php

namespace IO\Api;

/**
 * Class ResponseCode
 *
 * Enum with HTTP status codes.
 * @package IO\Api
 */
class ResponseCode
{
    /**
     * @var int CONTINUE HTTP status code 100 Continue.
     */
    const CONTINUE = 100;

    /**
     * @var int SWITCHING_PROTOCOLS HTTP status code 101 Switching Protocols.
     */
    const SWITCHING_PROTOCOLS = 101;

    /**
     * @var int PROCESSING HTTP status code 102 Processing.
     */
    const PROCESSING = 102; // RFC2518

    /**
     * @var int OK HTTP status code 200 OK.
     */
    const OK = 200;

    /**
     * @var int CREATED HTTP status code 201 Created.
     */
    const CREATED = 201;

    /**
     * @var int ACCEPTED HTTP status code 202 Accepted.
     */
    const ACCEPTED = 202;

    /**
     * @var int NON_AUTHORITATIVE_INFORMATION HTTP status code 203 Non-authoritative Information.
     */
    const NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * @var int NO_CONTENT HTTP status code 204 No Content.
     */
    const NO_CONTENT = 204;

    /**
     * @var int RESET_CONTENT HTTP status code 205 Reset Content.
     */
    const RESET_CONTENT = 205;

    /**
     * @var int PARTIAL_CONTENT HTTP status code 206 Partial Content.
     */
    const PARTIAL_CONTENT = 206;

    /**
     * @var int MULTI_STATUS HTTP status code 207 Multi-Status.
     */
    const MULTI_STATUS = 207; // RFC4918

    /**
     * @var int ALREADY_REPORTED HTTP status code 208 Already Reported.
     */
    const ALREADY_REPORTED = 208; // RFC5842

    /**
     * @var int IM_USED HTTP status code 226 IM Used.
     */
    const IM_USED = 226; // RFC3229

    /**
     * @var int MULTIPLE_CHOICES HTTP status code 300 Multiple Choices.
     */
    const MULTIPLE_CHOICES = 300;

    /**
     * @var int MOVED_PERMANENTLY HTTP status code 301 Moved Permanently.
     */
    const MOVED_PERMANENTLY = 301;

    /**
     * @var int FOUND HTTP status code 302 Found.
     */
    const FOUND = 302;

    /**
     * @var int SEE_OTHER HTTP status code 303 See Other.
     */
    const SEE_OTHER = 303;

    /**
     * @var int NOT_MODIFIED HTTP status code 304 Not Modified.
     */
    const NOT_MODIFIED = 304;

    /**
     * @var int USE_PROXY HTTP status code 305 Use Proxy.
     */
    const USE_PROXY = 305;

    /**
     * @var int RESERVED HTTP status code 306 Reserved.
     */
    const RESERVED = 306;

    /**
     * @var int TEMPORARY_REDIRECT HTTP status code 307 Temporary Redirect.
     */
    const TEMPORARY_REDIRECT = 307;

    /**
     * @var int PERMANENTLY_REDIRECT HTTP status code 308 Permanent Redirect.
     */
    const PERMANENTLY_REDIRECT = 308; // RFC7238

    /**
     * @var int BAD_REQUEST HTTP status code 400 Bad Request.
     */
    const BAD_REQUEST = 400;

    /**
     * @var int UNAUTHORIZED HTTP status code 401 Unauthorized.
     */
    const UNAUTHORIZED = 401;

    /**
     * @var int PAYMENT_REQUIRED HTTP status code 402 Payment Required.
     */
    const PAYMENT_REQUIRED = 402;

    /**
     * @var int FORBIDDEN HTTP status code 403 Forbidden.
     */
    const FORBIDDEN = 403;

    /**
     * @var int NOT_FOUND HTTP status code 404 Not Found.
     */
    const NOT_FOUND = 404;

    /**
     * @var int METHOD_NOT_ALLOWED HTTP status code 405 Method Not Allowed.
     */
    const METHOD_NOT_ALLOWED = 405;

    /**
     * @var int NOT_ACCEPTABLE HTTP status code 406 Not Acceptable.
     */
    const NOT_ACCEPTABLE = 406;

    /**
     * @var int PROXY_AUTHENTICATION_REQUIRED HTTP status code 407 Proxy Authentication Required.
     */
    const PROXY_AUTHENTICATION_REQUIRED = 407;

    /**
     * @var int REQUEST_TIMEOUT HTTP status code 408 Request Timeout.
     */
    const REQUEST_TIMEOUT = 408;

    /**
     * @var int CONFLICT HTTP status code 409 Conflict.
     */
    const CONFLICT = 409;

    /**
     * @var int GONE HTTP status code 410 Gone.
     */
    const GONE = 410;

    /**
     * @var int LENGTH_REQUIRED HTTP status code 411 Length Required.
     */
    const LENGTH_REQUIRED = 411;

    /**
     * @var int PRECONDITION_FAILED HTTP status code 412 Precondition Failed.
     */
    const PRECONDITION_FAILED = 412;

    /**
     * @var int REQUEST_ENTITY_TOO_LARGE HTTP status code 413 Payload Too Large.
     */
    const REQUEST_ENTITY_TOO_LARGE = 413;

    /**
     * @var int REQUEST_URI_TOO_LONG HTTP status code 414 Request-URI Too Long.
     */
    const REQUEST_URI_TOO_LONG = 414;

    /**
     * @var int UNSUPPORTED_MEDIA_TYPE HTTP status code 415 Unsupported Media Type.
     */
    const UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * @var int REQUESTED_RANGE_NOT_SATISFIABLE HTTP status code 416 Requested Range Not Satisfiable.
     */
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;

    /**
     * @var int EXPECTATION_FAILED HTTP status code 417 Expectation Failed.
     */
    const EXPECTATION_FAILED = 417;

    /**
     * @var int I_AM_A_TEAPOT HTTP status code 418 I'm a teapot.
     */
    const I_AM_A_TEAPOT = 418; // RFC2324

    /**
     * @var int UNPROCESSABLE_ENTITY HTTP status code 422 Unprocessable Entity.
     */
    const UNPROCESSABLE_ENTITY = 422; // RFC4918

    /**
     * @var int LOCKED HTTP status code 423 Locked.
     */
    const LOCKED = 423; // RFC4918

    /**
     * @var int FAILED_DEPENDENCY HTTP status code 424 Failed Dependency.
     */
    const FAILED_DEPENDENCY = 424; // RFC4918

    /**
     * @var int RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL HTTP status code Reserved for WebDAV advanced collections expired proposal.
     */
    const RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425; // RFC2817

    /**
     * @var int UPGRADE_REQUIRED HTTP status code 426 Upgrade Required.
     */
    const UPGRADE_REQUIRED = 426; // RFC2817

    /**
     * @var int PRECONDITION_REQUIRED HTTP status code 428 Precondition Required.
     */
    const PRECONDITION_REQUIRED = 428; // RFC6585

    /**
     * @var int TOO_MANY_REQUESTS HTTP status code 429 Too Many Requests.
     */
    const TOO_MANY_REQUESTS = 429; // RFC6585

    /**
     * @var int REQUEST_HEADER_FIELDS_TOO_LARGE HTTP status code 431 Request Header Fields Too Large.
     */
    const REQUEST_HEADER_FIELDS_TOO_LARGE = 431; // RFC6585

    /**
     * @var int UNAVAILABLE_FOR_LEGAL_REASONS HTTP status code 451 Unavailable For Legal Reasons.
     */
    const UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    /**
     * @var int INTERNAL_SERVER_ERROR HTTP status code 500 Internal Server Error.
     */
    const INTERNAL_SERVER_ERROR = 500;

    /**
     * @var int NOT_IMPLEMENTED HTTP status code 501 Not Implemented.
     */
    const NOT_IMPLEMENTED = 501;

    /**
     * @var int BAD_GATEWAY HTTP status code 502 Bad Gateway.
     */
    const BAD_GATEWAY = 502;

    /**
     * @var int SERVICE_UNAVAILABLE HTTP status code 503 Service Unavailable.
     */
    const SERVICE_UNAVAILABLE = 503;

    /**
     * @var int GATEWAY_TIMEOUT HTTP status code 504 Gateway Timeout.
     */
    const GATEWAY_TIMEOUT = 504;

    /**
     * @var int VERSION_NOT_SUPPORTED HTTP status code 505 HTTP Version Not Supported.
     */
    const VERSION_NOT_SUPPORTED = 505;

    /**
     * @var int VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL HTTP status code 506 Variant Also Negotiates.
     */
    const VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506; // RFC2295

    /**
     * @var int INSUFFICIENT_STORAGE HTTP status code 507 Insufficient Storage.
     */
    const INSUFFICIENT_STORAGE = 507; // RFC4918

    /**
     * @var int LOOP_DETECTED HTTP status code 508 Loop Detected.
     */
    const LOOP_DETECTED = 508; // RFC5842

    /**
     * @var int NOT_EXTENDED HTTP status code 510 Not Extended.
     */
    const NOT_EXTENDED = 510; // RFC2774

    /**
     * @var int NETWORK_AUTHENTICATION_REQUIRED HTTP status code 511 Network Authentication Required.
     */
    const NETWORK_AUTHENTICATION_REQUIRED = 511; // RFC6585
}
