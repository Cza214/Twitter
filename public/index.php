<?php

require_once(__DIR__ . './../src/Controller.php');

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$controller = new Controller();

echo '<!DOCTYPE html>';
echo '<html>';
echo $controller->showHead();
echo '<body>';

if($uri === '/login'){
    if($method === 'GET')
    {
        echo $controller->showLogin();

    } elseif($method === 'POST') {

        try {
            echo $controller->loginCheck();

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}
if($uri === '/register'){
    if($method === 'GET')
    {
        echo $controller->showRegister();
    } elseif($method === 'POST'){
        try {
            echo $controller->createUser();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}

if($uri === '/tweety'){
    if($method === 'GET'){
        $controller->tweets();
    } elseif($method === 'POST'){
        echo $controller->addTweet();
    }
}
if($uri === '/profile'){
    if($method === 'GET')
    {
        $controller->showProfile();
    } elseif($method === 'POST'){
        echo $controller->updateProfile();
    }
}
if($uri === '/logout'){
    if($method === 'GET'){
        $controller->logout();
    }
}
if($uri === '/'){
    if(isset($_SESSION['user']))
    {
        header('Location: /tweety');
    } else {
        header('Location: /login');
    }
}
echo $controller->showScripts();;
echo '</body>';
echo '</html>';