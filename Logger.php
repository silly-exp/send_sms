<?php
#TODO:
# gérer les niveaux de log
# gérer la configuration et les le nom du fichier de log
# gérer le cas des FS pour lesquels le verrou ne fonctionne pas.
# mettre en place des tests unitaires. 


class Logger{
    
    private static $instance = NULL;
    private $lf;

    private function __construct() {
        $this->lf = fopen("./application.log","w");
    }
    
    function __destruct() {
        fclose($this->lf);
    }
    
    public function getInstance() {
        // Instantiate itself if not instantiated
        if(self::$instance === NULL) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    public function log($level, $message) {
        if (flock($this->lf, LOCK_EX)) { // acquière un verrou exclusif
            fwrite($this->lf, $message);
            fflush($this->lf);            // libère le contenu avant d'enlever le verrou
            flock($this->lf, LOCK_UN);    // Enlève le verrou
        } else {
            echo "Impossible de verrouiller le fichier de log!";
        }
    }

};