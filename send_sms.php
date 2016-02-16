<?php
# TODO:
# -- envoyer vraiment le sms
# gérer les identifiants dans un fichier de config
# logger des informations => créer une classe de logger? => devrait être un singleton
# mettre en place des tests (unitaires ou pas...)
# Substituer les \n par des \r pour les retours à la ligne. (ou virer les \n?)
# Gérer correctement la sortie des cas d'erreur.
# utiliser des templates pour les renders.
# vérifier correctement le certificat https
# gérer le contrôle du fichier de configuration (mauvaise syntaxe, autre...)
# charger la config avant, ce n'est pas le travail du service d'envoie de le contrôler.
#
# Questions?
# Quelle est la limite de taille de message?
# Quelle est la limite de nombre de sms par unité de temps?
#----------------------------------------------------

require 'Logger.php';

$logger = Logger::getInstance();

function controler_send_mail(){
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        render_error("attend un formulaire envoyé en POST!");
        exit;
    }

    $message = $_REQUEST['message'];
    if (empty($message)) {
        render_error("Le paramètre 'message' non vide est attendu");
        exit;
    }
    $resp = service_send_sms($message, 'nico');
    if ($resp == false){
        render_error("problème lors de l'envoie du sms.");
        exit;
    }
    render_page($resp);
}

function service_send_sms($message, $contact){
    # chargement du fichier de config
    $json_config = file_get_contents("./users.conf");
    $config = json_decode($json_config, true);
    # FIXME: que se passe-t-il quand le contact n'est pas dans la config?
    $user_properties = $config($contact);
    $user_id = $user_properties['user_id'];
    $pass = $user_properties['passwsd'];
    
    $encoded_mess = urlencode($message);
    #$url = "https://smsapi.free-mobile.fr/sendmsg?user={$user_id}&pass={$pass}&msg={$encoded_mess}";
    $url = "https://google.com";
    $logger->log('DEBUG', "URL: " . $url);
    $curl_handle = curl_init($url);
    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, true);
    # FIXME: pas de vérification du certificat
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($curl_handle);
    $infos = curl_getinfo($curl_handle);
    $logger->log('DEBUG', "INFO SUR LA REQUETE: " . $infos);
    curl_close($curl_handle);
    return $response;
}


function render_page($message){
    echo "SEND SMS! </br>";
    echo "[$message]";
}

function render_error($message){
    echo "<div class=\"error\"> $message </div>";
}

controler_send_mail();

#TESTS
#service_send_sms("toto");