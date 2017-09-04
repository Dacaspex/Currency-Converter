<?php

/**
 * Authentication layer.
 * All functions regarding authentication are grouped in this class. It maintains
 * the session as well as the data base link.
 */
class Auth
{
    /** URL of the homepage */
    const HOME_URL = '/veldsink/';

    /** Database information */
    const DRIVER = 'mysql';
    const DB_HOST = 'localhost';
    const DB_DATABASE = 'currency_converter';
    const DB_USERNAME = 'root';
    const DB_PASSWORD = '';

    /** Database connection */
    private static $connection = null;
    /** Stores whether a session has been started */
    private static $sessionStarted = false;

    /**
     * Connects to the dataabse using PDO
     */
    public static function connect()
    {
        $args = self::DRIVER
            . ':host='
			. self::DB_HOST
			. ';dbname='
			. self::DB_DATABASE
			. ';charset=utf8mb4';

        try {
            self::$connection = new PDO($args, self::DB_USERNAME, self::DB_PASSWORD);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // TODO Better error handling
            var_dump($exception);
        }
    }

    /**
     * Starts a new session and regenrates the id
     */
    public static function setupSession()
    {
        self::$sessionStarted = true;
        session_start();
        session_regenerate_id();
    }

    /**
     * Validates email / password credentials
     * @param $email String The user email
     * @param $password String the user password
     * @return Bool True if validation was successful, false otherwise
     */
    public static function validate($email, $password)
    {
        if (!isset(self::$connection)) {
            self::connect();
        }

        $hashedPassword = hash('sha512', $password);

        // Build select statement
        $stmt = self::$connection->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->execute();

        // If no row was found, validation was unsuccessful
        if ($stmt->rowCount() == 0) {
            return false;
        }

        // Get user information
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!self::$sessionStarted) {
                self::setupSession();
            }
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['name'];
            return true;
        }
    }

    /**
     * @return Bool True when the user has been valdiated, false otherwise
     */
    public static function check()
    {
        if (!self::$sessionStarted) {
            self::setupSession();
        }
        return isset($_SESSION['id']);
    }

    /**
     * Logs out the user and redirects to the
     */
    public static function logout()
    {
        if (!self::$sessionStarted) {
            self::setupSession();
        }
        unset($_SESSION['id']);
        self::redirect(self::HOME_URL);
    }

    /**
     * Adds a message to the flash memory (read-once-memory).
     */
    public static function flash($message)
    {
        if (!self::$sessionStarted) {
            self::setupSession();
        }
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }

        $_SESSION['flash'][] = $message;
    }

    /**
     * Retrieves all messages in the flash memory and deletes them
     * @return Array All messages in the flash memory
     */
    public static function getMessages()
    {
        if (!self::$sessionStarted) {
            self::setupSession();
        }
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $messages = $_SESSION['flash'];
        $_SESSION['flash'] = [];
        return $messages;
    }

    /**
     * Returns the name of the user that is currently logged in
     * @return String The name of the user
     */
    public static function getUsername()
    {
        if (!self::$sessionStarted) {
            self::setupSession();
        }
        return $_SESSION['username'];
    }

    /**
     * Redirects to the location
     * @param $location String location to be redirect to
     */
    public static function redirect($location)
    {
        header('Location: ' . $location);
    }
}

?>
