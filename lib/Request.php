<?

class Request {

  public static function redirect($url) {
    if (substr($url, 0, 1) != "/" && substr($url, 0, 4) != "http")
      $url = "/".$url;

    header('Location: '.$url);
    exit();
  }

  public static function log() {
    // Log the request.
    if (self::isCron()) {
      Config::getLog()->writeCron();
    }
    else {
      Config::getLog()->write();
    }
  }

  public static function isCron() {
    return getEnv("CRON_MODE") ? true : false;
  }

  public static function getMethod() { return $_SERVER["REQUEST_METHOD"]; }
  public static function is($meth) { return (self::getMethod() == strtoupper($meth)); }

  public static function requireGet() {
    if ($_SERVER["REQUEST_METHOD"] != "GET")
      self::jsonError(405);
  }

  public static function requirePost() {
    if ($_SERVER["REQUEST_METHOD"] != "POST")
      self::jsonError(405);
  }

  public static function param($field, $required = true, $isNum = true, $positive = true) {
    if ($_SERVER["REQUEST_METHOD"] == "GET")
      $req = $_GET;
    else
      $req = $_POST;

    if (!isset($req[$field]) && $required) {
        self::jsonError(400, "Missing required parameter '" .$field."'.");
    }
    if (isset($req[$field]) && $isNum && !is_numeric($req[$field])) {
      self::jsonError(400, "Invalid integer value provided for parameter '" . $field . "'.");
    }
    if (isset($req[$field]) && $isNum && $positive && intval($req[$field]) <= 0) {
      self::jsonError(400, "Invalid integer value provided for parameter '" . $field . "'.");
    }
    if (!isset($req[$field]))
      return null;
    return ($isNum) ? intval($req[$field]) : strip_tags($req[$field]);
  }

  private static function isAssoc(array $arr)
  {
      if (array() === $arr) return false;
      return array_keys($arr) !== range(0, count($arr) - 1);
  }


 public static function json($object, $code = 200) {

    $jsonObject = null;
    if ($object instanceof Model3) {
      $jsonObject = $object->toAPI();
    }
    else if (is_array($object)) {

      $jsonObject = [];
      if (self::isAssoc($object)) {
        foreach ($object as $k=>$obj) {
          if ($obj instanceof Model3) {
            $jsonObject[$k] = $obj->toAPI();
          }
          else
            $jsonObject[$k] = $obj;
        }
      }
      else {
        foreach ($object as $obj) {
          if ($obj instanceof Model3) {
            $jsonObject[] = $obj->toAPI();
          }
          else
            $jsonObject[] = $obj;
        }
      }
    }
    else {
      $jsonObject = $object;
    }

    http_response_code($code);
    if (!headers_sent())
      header("Content-Type: application/json; charset=utf-8");
    echo(json_encode($jsonObject));
    exit();
  }

  public static function jsonError($code, $description = null) {
    if (!isset($description))
      $description = HTTP::getCodeDescription($code);
    $obj = ["code"=>$code, "description"=>$description];
    self::json($obj, $code);
  }

}
?>
