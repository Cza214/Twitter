<?php
/**
 * Created by PhpStorm.
 * User: cezary
 * Date: 02.11.17
 * Time: 19:39
 */

class Message
{
    private $id;
    private $sender_id;
    private $receiver_id;
    private $text;
    private $date;
    private $status;

    public function __construct()
    {
        $this->id = -1;
        $this->sender_id = "";
        $this->receiver_id = "";
        $this->text = "";
        $this->date = "";
        $this->status = 0;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSenderId()
    {
        return $this->sender_id;
    }

    /**
     * @param mixed $sender_id
     */
    public function setSenderId($sender_id)
    {
        $this->sender_id = $sender_id;
    }

    /**
     * @return mixed
     */
    public function getReceiverId()
    {
        return $this->receiver_id;
    }

    /**
     * @param mixed $receiver_id
     */
    public function setReceiverId($receiver_id)
    {
        $this->receiver_id = $receiver_id;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }
    public function saveToDB(PDO $conn){
        $date = new \DateTime();
        $date->setTimezone(new DateTimeZone('Europe/Warsaw'));
        $date = $date->format('Y-m-d H:i:s');

        if ($this->id === -1) {
            $this->setDate($date);
            $stmt = $conn->prepare('INSERT INTO `box`(`sender_id`,`receiver_id`,`text`,`status`) VALUES (:sender_id,:receiver_id,:text,:status)');
            $result = $stmt->execute([
                'receiver_id' => $this->getReceiverId(),
                'sender_id' => $this->getSenderId(),
                'text' => $this->getText(),
                'status' => 0

            ]);
            if ($result !== false) {
                $this->id = $conn->lastInsertId();
                return true;
            }
        } else {
            $stmt = $conn->prepare('UPDATE `box` SET status=:status WHERE id=:id');
            $result = $stmt->execute([
                'status' => 1,
                'id' => $this->getId()
            ]);
            if($result != false)
            {
                return true;
            }
        }
        return false;
    }
    static public function getMessagesCount(PDO $conn, $id){
        $stmt = $conn->prepare('SELECT * FROM `box` WHERE receiver_id=:id AND status=0');
        $res = $stmt->execute(['id' => $id]);
        $count = $stmt->rowCount();
        if($count > 0){
            return $count;
        }
    }
    static public function loadMessageByReceiverId(PDO $conn, $id) {
        $stmt = $conn->prepare('SELECT * FROM `box` WHERE receiver_id='.$id);
        $res = $stmt->execute();
        $tab = [];
        if($res !== false && $stmt->rowCount() > 0){
            foreach($stmt->fetchAll() as $key => $value){
                $box = new Message();
                $box->id = $value['id'];
                $box->setText($value['text']);
                $box->setSenderId($value['sender_id']);
                $box->setReceiverId($value['receiver_id']);
                $box->setDate($value['date']);
                $box->setStatus($value['status']);
                $tab[] = $box;
            }

            return $tab;
        }
        return NULL;

    }
    static public function loadMessageBySenderId(PDO $conn, $id){
        $stmt = $conn->prepare('SELECT * FROM `box` WHERE sender_id='.$id);
        $res = $stmt->execute();
        $tab = [];
        if($res !== false && $stmt->rowCount() > 0){
            foreach($stmt->fetchAll() as $key => $value){
                $box = new Message();
                $box->id = $value['id'];
                $box->setText($value['text']);
                $box->setSenderId($value['sender_id']);
                $box->setReceiverId($value['receiver_id']);
                $box->setDate($value['date']);
                $box->setStatus($value['status']);
                $tab[] = $box;
            }

            return $tab;
        }
        return NULL;
    }
    static public function loadMessageById(PDO $conn, $id){
        $stmt = $conn->prepare('SELECT * FROM `box` WHERE id='.$id);
        $res = $stmt->execute();
        if($res !== false && $stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $box = new Message();
            $box->id = $row['id'];
            $box->setSenderId($row['sender_id']);
            $box->setDate($row['date']);
            $box->setText($row['text']);
            $box->setReceiverId($row['receiver_id']);
            $box->setStatus($row['status']);
            return $box;
        }
        return NULL;
    }


}