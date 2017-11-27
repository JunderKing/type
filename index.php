<?php
$action = $_GET['action'];
$index = new Index;
$result = $index->$action();
echo json_encode($result);

class Index {
    private $serverName = '127.0.0.1';
    private $userName = 'myuser';
    private $passwd = 'youxiwang';
    private $conn;

    public function __construct() {
        if (!$this->conn) {
            $this->conn = mysqli_connect($this->serverName, $this->userName, $this->passwd) or die('mysql connect error!');
            mysqli_select_db($this->conn, 'kingco_typing');
        }
    }

    private function getParam($keyArr) {
        $result = array();
        foreach ($keyArr as $key) {
            $result[$key] = $_POST[$key];
        }
        return $result;
    }

    public function addPhrase() {
        $paramArr = $this->getParam(array('userId', 'phrase', 'desc', 'firstClass', 'secondClass', 'thirdClass', 'level'));
        extract($paramArr);
        $curTime = time();
        $sql = 'INSERT INTO tp_phrase(`user_id`, `phrase`, `desc`, `first_class`, `second_class`, `third_class`, `level`, `created_at`, `updated_at`)' .
            "VALUES($userId, '$phrase', '$desc', '$firstClass', '$secondClass', '$thirdClass', $level, $curTime, $curTime)";
        $result = mysqli_query($this->conn, $sql);
        if ($result === false) {
            $errno = 1;
        } else {
            $errno = 0;
        }
        return array(
            'errno' => $errno,
        );
    }
    
    public function updPhrase() {
        $paramArr = $this->getParam(array('userId', 'phraseId', 'spendTime', 'isCorrect'));
        extract($paramArr);

        //更新当前词组
        $sql = "UPDATE tp_phrase SET complete_count=complete_count+1";
        if (!$isCorrect) {
            $sql .= ', error_count=error_count+1';
        }
        $sql .= " WHERE id=$phraseId";
        $result = mysqli_query($this->conn, $sql);

        //获取新词组
        $sql = "SELECT * FROM tp_phrase WHERE user_id=$userId ORDER BY complete_count, updated_at LIMIT 1";
        $result = mysqli_query($this->conn, $sql);
        $resArr = mysqli_fetch_assoc($result);
        return array(
            'errno' => 0,
            'data' => $resArr
        );
    }
}
