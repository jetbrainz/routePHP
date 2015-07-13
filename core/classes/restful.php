<?php

class RESTful
{
	const STATUS_OK 				= '200 OK';
	const STATUS_CREATED			= '201 Created';
	const STATUS_NOCONTENT			= '204 No Content';

	const STATUS_NOTMODIFIED		= '304 Not Modified';

	const STATUS_BADREQUEST			= '400 Bad Request';
	const STATUS_UNAUTHORIZED		= '401 Unauthorized';
	const STATUS_FORBIDDEN			= '403 Forbidden';
	const STATUS_NOTFOUND			= '404 Not Found';
	const STATUS_METHODNOTALLOWED	= '405 Method Not Allowed';
	const STATUS_NOTACCEPTABLE		= '406 Not Acceptable';
	const STATUS_CONFLICT			= '409 Conflict';


	const STATUS_ERROR = '500 Internal Server Error';

	const DATATYPE_JSON = 'json';

	public static function answer($status, $data=null, $type=self::DATATYPE_JSON)
	{
		header ('Status: '.$status, true);
		if ($status == self::STATUS_UNAUTHORIZED) {
			header ('WWW-Authenticate: Basic realm="API"');
		}
		if ($data) {
			echo self::convertData($data, $type);
		}
	}

	private function convertData($data, $type)
	{
		if ($type == self::DATATYPE_JSON) {
			return json_encode($data);
		}
	}
}
