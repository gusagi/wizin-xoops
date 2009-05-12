<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

require_once 'Text/Pictogram/Mobile/Exception.php';

/**
 * Text_Pictogram_Mobile_Common
 * 
 * @category Text
 * @package  Text_Pictogram_Mobile
 * @author   Daichi Kamemoto <daikame@gmail.com>
 */
abstract class Text_Pictogram_Mobile_Common
{
    protected
        $carrier = null,
        $pictograms = null,
        $pictogramType = null,
        $characterEncode = 'sjis-win',
        $intercodePrefix = null,
        $intercodeSuffix = null,
        $convertDatabase = null,
        $escapeSequence = '&',
        $picdbDir = null;
    protected static
        $pictogramSeparater = ';';

    /**
     * Initialize
     * 絵文字データベースから絵文字を取得し、オブジェクトにセットする
     */
    public function initialize($db_dir = null)
    {
        if (isset($db_dir) && $db_dir) {
            $this->setPicdbDir($db_dir);
        } else {
            $this->setPicdbDir(dirname(__FILE__) . '/data');
        }

        $this->getEscapeSequence('&');
        $this->setIntercodePrefix('[({');
        $this->setIntercodeSuffix('})]');

        $this->loadPictograms($this->getCarrier());
        $this->loadConvertDB($this->getCarrier());
    }

    /**
     * 保持している絵文字のデータをすべてバイナリの配列として返す。
     * 引数のキャリアは変換の基準となるキャリア。
     *
     * @param string $carrier
     *
     * @return array
     */
    public function getFormattedPictogramsArray($carrier = 'docomo')
    {
        if ($this->getCarrier() == $carrier) {
            foreach ($this->pictograms[$carrier] as $number => $pictogram) {
                $binaryPictograms[$number] = pack('H*', (string)$pictogram);
            }
        } else {
            $this->loadConvertDB($carrier);
            foreach ($this->convertDatabase[$carrier] as $number => $convertNumbers) {
                $convertNumber = $convertNumbers[$this->getCarrier()];
                $binaryPictograms[$number] = $this->getPictogram($convertNumber);
            }
        }

        return $binaryPictograms;
    }

    /**
     * キャリア名を返す
     *
     * @return string
     */
    public function getCarrier()
    {
        return $this->carrier;
    }
    
    /**
     * 返却する絵文字のタイプを指定する。
     *
     * @param string $type
     */
    public function setPictogramType($type)
    {
        $this->pictogramType = $type;
    }

    /**
     * 返却する絵文字のタイプを返す。
     *
     * @return string
     */
    public function getPictogramType()
    {
        return $this->pictogramType;
    }

    /**
     * 内部絵文字に使用するPrefixを設定する。
     *
     * @param string $prefix
     */
    public function setIntercodePrefix($prefix)
    {
        $this->intercodePrefix = $prefix;
    }

    /**
     * 内部絵文字に使用するPrefixを取得する。
     *
     * @return string
     */
    public function getIntercodePrefix()
    {
        return $this->intercodePrefix;
    }

    /**
     * 内部絵文字に使用するSuffixを設定する。
     *
     * @param string $suffix
     */
    public function setIntercodeSuffix($suffix)
    {
        $this->intercodeSuffix = $suffix;
    }

    /**
     * 内部絵文字に使用するSuffixを取得する。
     *
     * @return string
     */
    public function getIntercodeSuffix()
    {
        return $this->intercodeSuffix;
    }

    /**
     * 内部絵文字のPrefix, Suffixを設定する。
     *
     * @param string $prefix
     * @param string $suffix
     */
    public function setIntercode($prefix, $suffix)
    {
        $this->intercodePrefix($prefix);
        $this->intercodeSuffix($suffix);
    }

    /**
     * 絵文字番号から、自身のキャリアの絵文字バイナリを返す。
     * 数字以外が入ってきたときはすべてそのまま返す。
     *
     * @param integer $number
     *
     * @return string
     */
    public function getPictogram($picNumber)
    {
        // セパレータでつながっている場合は、分割して複数返す。
        #TODO セパレータで 数字+文字というつなぎだった場合を考慮すると、_getPictogramの分割の仕方はおかしいかも？後で考慮
        $picBinary = '';
        $picUnpacked = '';
        if (strpos($picNumber, self::getPictogramSeparater())) {
            $picNumberList = explode(self::getPictogramSeparater(), $picNumber);
            foreach ($picNumberList as $number) {
                $picUnpacked .= $this->_getPictogram($number);
            }
            $picBinary = $this->toBinary($picUnpacked);
        } else if (is_numeric($picNumber)) {
            $picUnpacked = $this->_getPictogram($picNumber);
            $picBinary = $this->toBinary($picUnpacked);
        } else {
            $inputEncoding = mb_detect_encoding($picNumber, 'UTF-8, sjis-win, jis');
            if ($inputEncoding != $this->getCharacterEncoding()) {
                $picBinary = mb_convert_encoding($picNumber, $this->getCharacterEncoding(), $inputEncoding);
            } else {
                $picBinary = $picNumber;
            }
        }

        return $picBinary;
    }

    /**
     * 絵文字番号から、絵文字のバイナリ文字列を返す。数字じゃなかったらそのまま返す。
     *
     * @param integer $number
     *
     * @return string $unpackedChar
     */
    protected function _getPictogram($number)
    {
        if (array_key_exists($number, $this->pictograms[$this->getCarrier()])) {
            $return = $this->pictograms[$this->getCarrier()][$number];
        } else {
            $return = '';
        }

        return $return;
    }

    /**
     * 絵文字のunpackされた値から絵文字番号を取得する
     *
     * @param string $unpackedChar
     *
     * @return integer
     */
    protected function getPictogramNumber($unpackedChar)
    {
        return array_search($unpackedChar, $this->pictograms[$this->getCarrier()]);
    }

    /**
     * 使用している文字エンコードを取得
     *
     * @return string
     */
    public function getCharacterEncoding()
    {
        return $this->characterEncode;
    }

    /**
     * マルチバイト文字かどうかを判定
     *
     * @param string $unpackedChar
     *
     * @return boolian
     */
    public function isMultibyte($unpackedChar)
    {
        if (function_exists('mb_check_encoding')) {
            $return = mb_check_encoding($this->toBinary($unpackedChar), $this->getCharacterEncoding());
            #TODO: できれば、判定するのをpackせずにやりたい。
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     * エスケープシーケンスを設定
     */
    protected function setEscapeSequence($escapeSequence)
    {
      $this->escapeSequence = $escapeSequence;
    }


    /**
     * エスケープシーケンスを取得
     */
    protected function getEscapeSequence()
    {
        return $this->escapeSequence;
    }

    /**
     * エスケープシーケンスと、内部文字コードのPrefix/Suffixと同じ文字列をエスケープする
     *
     * @param string $inputString
     *
     * @return string
     */
    public function escapeString($inputString)
    {
        if (strpos($inputString, $this->getEscapeSequence())) {
            $inputString = str_replace($this->getEscapeSequence(), $this->_escapeString($this->getEscapeSequence()), $inputString);
        }
        if (strpos($inputString, $this->getIntercodePrefix()) !== false) {
            $inputString = str_replace($this->getIntercodePrefix(), $this->_escapeString($this->getIntercodePrefix()), $inputString);
        }
        if (strpos($inputString, $this->getIntercodeSuffix()) !== false) {
            $inputString = str_replace($this->getIntercodeSuffix(), $this->_escapeString($this->getIntercodeSuffix()), $inputString);
        }
        $return = $inputString;

        return $return;
    }

    /**
     * 渡された文字列をエスケープシーケンスを使ってエスケープする
     *
     * @param string $inputString
     *
     * @return string
     */
    protected function _escapeString($inputString)
    {
        if (strlen($inputString) === 1) {
            $return = $this->getEscapeSequence() . $inputString;
        } else if (strlen($inputString) > 1) {
            $splitStringArray = str_split($inputString);
            $return = $this->getEscapeSequence() . implode($this->getEscapeSequence(), $splitStringArray);
        } else {
            $return = $inputString;
        }

        return $return;
    }

    /**
     * エスケープを元に戻す
     *
     * @param string $inputString
     *
     * @return string
     */
    public function unescapeString($inputString)
    {
        if (strpos($inputString, $this->_escapeString($this->getIntercodePrefix())) !== false) {
            $inputString = str_replace($this->_escapeString($this->getIntercodePrefix()), $this->getIntercodePrefix(), $inputString);
        }
        if (strpos($inputString, $this->_escapeString($this->getIntercodeSuffix())) !== false) {
            $inputString = str_replace($this->_escapeString($this->getIntercodeSuffix()), $this->getIntercodeSuffix(), $inputString);
        }
        if (strpos($inputString, $this->_escapeString($this->getEscapeSequence()))) {
            $inputString = str_replace($this->_escapeString($this->getEscapeSequence()), $this->getEscapeSequence(), $inputString);
        }
        $return = $inputString;

        return $return;
    }

    /**
     * 絵文字データベースのパスを取得
     *
     * @return string picdbDir
     */
    public function getPicdbDir()
    {
        return $this->picdbDir;
    }

    public function setPicdbDir($dir)
    {
        if (!(file_exists($dir) && is_dir($dir) && $realpath = realpath($dir))) {
            throw new Text_Pictogram_Mobile_Exception('Database file not found:' . $dir);
        }

        $this->picdbDir = $realpath;
    }
    /**
     * 絵文字データをデータベースからロードする。
     *
     * @param string $carrier
     */
    protected function loadPictograms($carrier)
    {
        if (isset($this->pictograms[$carrier]) && !empty($this->pictograms[$carrier])) return;

        $filename = $this->getPicdbDir() . '/' . $carrier . '_emoji.json';
        if (!file_exists($filename)) {
            throw new Text_Pictogram_Mobile_Exception('pictograms file not found:' . $filename);
        }

        $json = file_get_contents($filename);
        $pictograms = json_decode($json, true);
        foreach ($pictograms[$carrier] as $data) {
            $this->pictograms[$carrier][$data['number']] = $data[$this->getPictogramType()];
        }
    }

    /**
     * 変換データをデータベースからロードする。
     *
     * @param string $carrier
     */
    protected function loadConvertDB($carrier)
    {
        if (isset($this->convertDatabase[$carrier]) && !empty($this->convertDatabase[$carrier])) return;

        $filename = $this->getPicdbDir() . '/' . $carrier . '_convert.json';
        if (!file_exists($filename)) {
            throw new Text_Pictogram_Mobile_Exception('convert file not found:' . $filename);
        }

        $json = file_get_contents($filename);
        $convert = json_decode($json, true);
        $this->convertDatabase[$carrier] = $convert[$carrier];
    }


    /**
     * 複数絵文字組み合わせの場合のセパレータを返す
     * あんまり意味ない気がするけど、とりあえず。
     *
     * @return string
     */
    protected static function getPictogramSeparater()
    {
        return self::$pictogramSeparater;
    }

    /**
     * 内部絵文字から絵文字を返す。
     * ただし、入力文字はprefixとsuffixをとりぞのいたもの。。。にしてるけど、どうだろ。
     * 
     * @param string $intercode
     *
     * @return string
     */
    protected function getPictogramIntercode($intercode)
    {
        list($carrierCode, $sourcePicNumber) = explode(' ', trim($intercode));

        #TODO: 今のままではcarrierCodeが絵文字DB依存。ここを疎にしたい。。。意味は無いかも。
        $sourceCarrier = $carrierCode;
        if (strtolower($sourceCarrier) != strtolower($this->getCarrier())) {
            $this->loadConvertDB($sourceCarrier);
            $picNumber = $this->convertDatabase[$sourceCarrier][$sourcePicNumber][$this->getCarrier()];
        } else {
            $picNumber = $sourcePicNumber;
        }

        return $this->getPictogram($picNumber);
    }

    /**
     * 絵文字のバイナリ文字列を内部絵文字へ変換
     *
     * @param string $unpackedChar
     *
     * @return string
     */
    abstract protected function toIntercodeUnpacked($unpackedChar);

    /**
     * 絵文字バイナリを内部絵文字へ変換
     *
     * @param string $char
     *
     * @return string
     */
    abstract public function toIntercode($char);

    /**
     * unpack済みのバイナリが絵文字に相当するかどうかを判定する
     *
     * @param string $unpackedChar
     * @param string $carrier
     *
     * @return boolian
     */
    protected function isPictogramUnpacked($unpackedChar, $carrier = null)
    {
        if (is_null($carrier)) {
            $carrier = $this->getCarrier();
        }
        return in_array($unpackedChar, $this->pictograms[$carrier]);
    }

    /**
     * 絵文字かどうかを判定する
     * 
     * @param string $char
     *
     * @return boolian
     */
    abstract public function isPictogram($char);

    /**
     * 文字列全体を解析し、絵文字を内部絵文字に置換した文字列を返す
     *
     * @param string $inputString
     *
     * @return string
     */
    abstract public function convert($inputString);

    public function _convertSJIS($inputString)
    {
        if (!strlen($inputString)) return $inputString;

        $convertString = "";
        $nonPicStorage = "";

        $binaryArrayObject = new ArrayObject(str_split(strtoupper(bin2hex($inputString)), 2));
        $iterator = $binaryArrayObject->getIterator();
        while ($iterator->valid()) {
            $firstByte = $iterator->current();
            $iterator->next();
            if (!$iterator->valid()) {
                $nonPicStorage .= $firstByte;
                break;
            }
            $checkString = $firstByte . $iterator->current();
            if ($this->isPictogramUnpacked($checkString)) {
                if (!empty($nonPicStorage)) {
                    // 非絵文字配列をバイナリ化。１字ずつやると妙に重いため、絵文字節ごとにまとめて処理。
                    $convertString .= $this->toBinary($nonPicStorage);
                    $nonPicStorage = "";
                }
                $convertString .= $this->toIntercodeUnpacked($checkString);
                $iterator->next();
            } else if ($this->isMultibyte($checkString)) {
                $nonPicStorage .= $checkString;
                $iterator->next();
            } else {
                $nonPicStorage .= $firstByte;
            }
        }
        // 後処理
        if (!empty($nonPicStorage)) {
            $convertString .= $this->toBinary($nonPicStorage);
        }

        return $convertString;
    }

    public function _convertUTF8($inputString) 
    {
        if (!strlen($inputString)) return $inputString;

        $pictogramAreaFlags = array('EE', 'EF');

        $binaryArrayObject = new ArrayObject(str_split(strtoupper(bin2hex($inputString)), 2));
        $iterator = $binaryArrayObject->getIterator();

        $replaceBinary = '';
        $replaceString = '';

        while ($iterator->valid()) {
            $checkString = $iterator->current();
            $iterator->next();
            if (!$iterator->valid()) {
                $replaceBinary .= $checkString;
                break;
            }

            // Pictogram Flag Check
            if (!in_array($checkString, $pictogramAreaFlags)) {
                $replaceBinary .= $checkString;
                continue;
            }

            $checkString .= $iterator->current();
            $iterator->next();
            if (!$iterator->valid()) {
                $replaceBinary .= $checkString;
                break;
            }

            $checkString .= $iterator->current();


            if ($this->isPictogramUnpacked($checkString)) {
                $replaceString .= pack('H*', $replaceBinary);
                $replaceString .= $this->toIntercodeUnpacked($checkString);
                $replaceBinary = "";
            } else {
                $replaceBinary .= $checkString;
            }

            $iterator->next();
        }

        if ($replaceBinary) {
            $replaceString .= pack('H*', $replaceBinary);
        }

        return $replaceString;
    }

        

    /**
     * UTF-8文字列を解析し、絵文字を現在のキャリア用に置換した文字列を返す
     *
     * @param string $inputString
     * 
     * @return string
     */
    public function replace($inputString)
    {
        if (!strlen($inputString)) return $inputString;

        $pictogramAreaFlags = array('EE', 'EF');

        $binaryArrayObject = new ArrayObject(str_split(strtoupper(bin2hex($inputString)), 2));
        $iterator = $binaryArrayObject->getIterator();

        $replaceBinary = '';
        while ($iterator->valid()) {
            $checkString = $iterator->current();
            $iterator->next();
            if (!$iterator->valid()) {
                $replaceBinary .= $checkString;
                break;
            }

            // Pictogram Flag Check
            if (!in_array($checkString, $pictogramAreaFlags)) {
                $replaceBinary .= $checkString;
                continue;
            }

            $checkString .= $iterator->current();
            $iterator->next();
            if (!$iterator->valid()) {
                $replaceBinary .= $checkString;
                break;
            }

            $checkString .= $iterator->current();
            $replaceBinary .= $this->_replace($checkString);

            $iterator->next();
        }

        $replaceString = pack('H*', $replaceBinary);

        return $replaceString;
    }

    /**
     * UTF-8絵文字かどうかを判定して、そうだったらキャリアを割り出して切り替える。
     *
     * @param string $checkString
     * 
     * @return string
     */
    protected function _replace($checkString)
    {
        #TODO: キャリア名リテラルなんとかする。
        $carriers = array('docomo', 'ezweb', 'softbank');

        $return = null;
        foreach ($carriers as $carrier) {
            $this->loadPictograms($carrier);
            if (!$this->isPictogramUnpacked($checkString, $carrier)) continue;
            if ($this->getCarrier() == $carrier) {
                $return = $checkString;
                break;
            }
            if (!($sourceNumber = array_search($checkString, $this->pictograms[$carrier]))) continue;

            $this->loadConvertDB($carrier);
            $replace = $this->convertDatabase[$carrier][$sourceNumber][$this->getCarrier()];

            // getPictogramの移植。toBinaryが必要ないので。。。TODO: なんとかする。
            $picUnpacked = "";
            if (strpos($replace, self::getPictogramSeparater())) {
                $replaceList = explode(self::getPictogramSeparater(), $replace);
                foreach ($replaceList as $number) {
                    $picUnpacked .= $this->_getPictogram($number);
                }
            } else if (is_numeric($replace)) {
                $picUnpacked = $this->_getPictogram($replace);
            } else {
                $picUnpacked = bin2hex($replace);
            }

            $return =  $picUnpacked;
            break;
        }

        if (is_null($return)) {
            $return = $checkString;
        }

        return $return;
    }

    /**
     * 絵文字を削除する
     *
     * @param string $inputString
     *
     * @return string
     */
    public function erase($inputString)
    {
        switch ($this->getPictogramType()) {
            case "sjis":
                break;
            case "utf-8":
                $result = $this->_eraseUTF8($inputString);
                break;
            default:
                $result = $inputString;
                break;
        }

        return $result;
    }

    protected function _eraseUTF8($inputString)
    {
        if (!strlen($inputString)) return $inputString;

        $pictogramAreaFlags = array('EE', 'EF');

        $binaryArrayObject = new ArrayObject(str_split(strtoupper(bin2hex($inputString)), 2));
        $iterator = $binaryArrayObject->getIterator();

        $replaceBinary = '';
        while ($iterator->valid()) {
            $checkString = $iterator->current();
            $iterator->next();
            if (!$iterator->valid()) {
                $replaceBinary .= $checkString;
                break;
            }

            // Pictogram Flag Check
            if (!in_array($checkString, $pictogramAreaFlags)) {
                $replaceBinary .= $checkString;
                continue;
            }

            $checkString .= $iterator->current();
            $iterator->next();
            if (!$iterator->valid()) {
                $replaceBinary .= $checkString;
                break;
            }

            $checkString .= $iterator->current();
            if (!$this->isPictogramUnpacked($checkString)) {
                $replaceBinary .= $checkString;
            }

            $checkString = '';
            $iterator->next();
        }

        $replaceString = pack('H*', $replaceBinary);

        return $replaceString;
    }

    /**
     * 内部絵文字を含む文字列を渡し、内部絵文字を各機種の絵文字に置換した文字列を返す
     *
     * @param string $inputString
     *
     * @return string
     */
    public function restore($inputString)
    {
        $restoreString = $this->unescapeString($this->_restore($inputString));
        return $restoreString;
    }

    protected function _restore($inputString)
    {
        if (!strlen($inputString)) return $inputString;

        // 内部絵文字に該当する部分だけを抽出
        $pattern = '/' . preg_quote($this->getIntercodePrefix(), '/') . '(.*)' . preg_quote($this->getIntercodeSuffix(), '/') . '/mUs';
        preg_match_all($pattern, $inputString, $matches, PREG_SET_ORDER);

        $replaceArray = array();
        foreach ($matches as $match) {
            $replaceArray[$match[0]] = $this->getPictogramIntercode(trim($match[1]));
        }

        if (count($replaceArray)) {
            $restoreString = strtr($inputString, $replaceArray);
        } else {
            $restoreString = $inputString;
        }

        return $restoreString;
    }

}
