<?php

class Comment{

    private $id;
    private $userId;
    private $postId;
    private $creationDate;
    private $text;

    public function __construct()
    {
        $this->id = -1;
        $this->userId = "";
        $this->postId = "";
        $this->creationDate = "";
        $this->text = "";
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
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * @param mixed $postId
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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
    public function saveToDB(PDO $conn)
    {
        $date = new \DateTime();
        $date->setTimezone(new DateTimeZone('Europe/Warsaw'));
        $date = $date->format('Y-m-d H:i:s');

        if ($this->id === -1) {
            $this->setCreationDate($date);
            $stmt = $conn->prepare('INSERT INTO `comment`(`post_id`,`user_id`,`text`,`creationDate`) VALUES (:post_id,:user_id,:text,:creationDate)');
            $result = $stmt->execute([
                'user_id' => $this->getUserId(),
                'text' => $this->getText(),
                'creationDate' => $this->getCreationDate(),
                'post_id' => $this->getPostId()

            ]);
            if ($result !== false) {
                $this->id = $conn->lastInsertId();
                return true;
            }
            return false;
        }
        return false;
    }
    static public function countOfCommentsByPostId(PDO $conn,$post_id){
        $stmt = $conn->prepare('SELECT * FROM `comment` WHERE post_id=:id');
        $res = $stmt->execute(['id' => $post_id]);
        if($stmt->rowCount() > 0)
        {
            return $stmt->rowCount();
        } else {
            return"";
        }
    }
    static public function loadCommentById(PDO $conn, $id){
        $stmt = $conn->prepare('SELECT * FROM `comment` WHERE id=:id');
        $result = $stmt->execute([ 'id' => $id]);
        if($result !== false and $stmt->rowCount() > 0){
            $res = $stmt->fetch( PDO::FETCH_ASSOC);
            $comment = new Comment();
            $comment->setUserId($res['user_id']);
            $comment->setText($res['text']);
            $comment->setCreationDate($res['creationDate']);
            $comment->id = $res['id'];
            $comment->setPostId($res['post_id']);
            return $comment;
        }
        return NULL;
    }
    static public function loadAllCommentsByPostId(PDO $conn, $post_id){
        $stmt = $conn->prepare('SELECT * FROM `comment` WHERE post_id=:id');
        $result = $stmt->execute([ 'id' => $post_id]);
        $tab = [];
        if($result !== false and $stmt->rowCount() > 0){
            foreach ($stmt->fetchAll() as $key)
            {
                $comment = new Comment();
                $comment->setUserId($key['user_id']);
                $comment->setText($key['text']);
                $comment->setCreationDate($key['creationDate']);
                $comment->id = $key['id'];
                $comment->setPostId($key['post_id']);
                $tab[] = $comment;
            }
            return $tab;
        }
        return NULL;
    }


}