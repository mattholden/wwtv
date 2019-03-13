<?

class Tools {

    public static function formatPhone($phone, $country) {

        $phone = preg_replace("/[^A-Za-z0-9]/", "",$phone);
        return ( ($country == "USA" || $country == "Canada") ? "1" : "") . $phone;
    }

    public static function monthName($mo) {
        $month = [
            "none",
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        ];
        return $month[$mo];
    }

    public static function scrape($file, $start, $end) {
        //echo($start . " => " . $end. "<br>");
        if ($end >= strlen($file) || $end < 0)
            return "";
        $startCount = strpos($file, $start) + strlen($start);
        $endCount = strpos($file, $end, $startCount);
        return substr($file, $startCount, $endCount - $startCount);
    }

    public static function randomPassword($len) {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $len; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string

    }

    public static function cap($str, $len) {
        $ellipsis = (strlen($str) > $len) ? "..." : "";
        return substr($str, 0, min($len, strlen($str))) . $ellipsis;
    }

    /** For use in text fields, because only the double-quote is dangerous here.
     * Stuff like htmlspecialchars() will also escape single quotes/apostrophes, ampersands,
     * etc. which will lead to them being saved in the DB and result in unpredictable displays.
     * @param $str String to requote
     * @return dequoted string
     */
    public static function dequote($str) {
        return str_replace('"', '', $str);
    }

    public static function integer($str) {
        return is_integer($str) ? intval($str) : null;
    }

    public static function toUSD($data, $dollarSign = true) {
        if ($data === null)
            return null;

        if ($data < 0) {
            $dollars = ($data / 100.0) * -1;
            return "-" . ($dollarSign ? " $":"") .number_format($dollars, 2);
        }
        else {
            $dollars = $data / 100.0;
            return ($dollarSign ? "$":"") . number_format($dollars,2);
        }
    }

    public static function arrayToCSV($array, $columns, $headers = null)
    {
        $END = '"';
        $SEP = '","';

        $header = "";
        $hfields = 0;

        if ($headers === null)
            $headers = $columns;

        foreach ($headers as $head) {
            $header .= ($hfields == 0) ? "" : ",";
            $hfields++;
            $header .= '"' . self::dequote($head) . '"';
        }

        $header .= chr(10);
        print($header);

        foreach ($array as $obj) {
            $row = $END;

            foreach ($columns as $col) {
                if ($row != $END)
                    $row .= $SEP;
                $row .= htmlspecialchars($obj[$col]);
            }
            $row .= $END;
            $row .= chr(10);
            print($row);
        }
    }
}
?>
