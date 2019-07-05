<?php
namespace Dockerize\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Composer\XdebugHandler\PhpConfig;
use Cake\Core\Configure\Engine\PhpConfig as CakePhpConfig;
use Cake\Filesystem\File;

/**
 * Setup shell command.
 */
class SetupShell extends Shell
{
    /**
     * Define list of configuration lines that docker will replace with local
     * configuration elements required by cakephp3-docker to run the application.
     *
     * @var array
     */
    private $_config = [
        "'host' => 'localhost'," => "'host' => env('MYSQL_HOST'),",
        "'username' => 'my_app'," => "'username' => env('MYSQL_USER'),",
        "'password' => 'secret'," => "'password' => env('MYSQL_PASSWORD'),",
        "'database' => 'my_app'," => "'database' => env('MYSQL_DATABASE'),"
    ];
    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->setDescription(__d(
            "dockerie", 
            "Provide useful methods necessary to check cakephp3-docker config."
        ))->addSubcommand("check", [
            "help" => "Check if current app has correct docker configuration."
        ])->addSubcommand("exec", [
            "help" => "Change config/app.php, if possible, to get it working with docker."
        ])->addSubcommand("env", [
            "help" => "Show currently configured environment variables."
        ]);
        
        return $parser;
    }

    /**
     * Check if current application has valid docker configuration data.
     *
     * @return void
     */
    public function check() {
        $configPath = ROOT . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "app.php";
        $this->out("Loading config: $configPath");
        $configFile = new File($configPath);
        $configText = $configFile->read();

        $keys = array_keys($this->_config);
        $wrong = FALSE;
        foreach($keys as $key) {
            $defaultKeyExists = strpos($configText, $key) !== FALSE;
            $newKeyExists = strpos($configText, $this->_config[$key]) !== FALSE;
            if ($defaultKeyExists && !$newKeyExists) {
                $this->out("<warning>The key $key should be updated with: {$this->_config[$key]}</warning>");
                $wrong = TRUE;
            }
        }
        
        if ($wrong) {
            $this->out(
                "<warning>" . 
                "You config/app.php should be update to work with docker! " .
                "Run bin/cake Dockerize.setup exec to change the file." . 
                "</warning>"
            );
        } 
    }

    /**
     * Exec setup, if possible, and replace configuration settings into config/app.php file.
     *
     * @return void
     */
    public function exec() {
        $path = ROOT . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "app.php";
        $file = new File($path);
        $text = $file->read();
        $keys = array_keys($this->_config);
        $existingCount = 0;
        $this->out("Checking $path consistency...");

        foreach($keys as $key) {
            $exists = strpos($text, $key) !== FALSE;
            $existingCount += ($exists ? 1 : 0);    
        }
        if ($existingCount !== count($keys)) {
            $this->out(
                "<error>" .
                "Not all configuration keys are default, " . 
                "Dockerize cannot update your file automatically." . 
                "</error>"
            );
        }
        else {
            $this->out("Replacing default Datasources.default keys...");
            foreach($keys as $key) {
                $text = str_replace($key, $this->_config[$key], $text);
            }
            $this->out("Writing new $path file...");
            $file->write($text);
            $file->close();
            $this->out("Operation completed.");
        }
    }
    
    /**
     * Show currently value for environemnt variables.
     *
     * @return void
     */
    public function env() {
        $host = env("MYSQL_HOST");
        $username = env("MYSQL_USER");
        $password = env("MYSQL_PASSWORD");
        $database = env("MYSQL_DATABASE");
        $this->out("Currently ENVIRONMENT vars:");
        $this->out("Host......: $host");
        $this->out("Database..: $database");
        $this->out("Username..: $username");
        $this->out("Password..: $password");
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        $this->out($this->OptionParser->help());
    }
}
