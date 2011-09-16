<?php
/**
 * The cron daily script.
 */
class Cron_Daily
{
    /**
     * The file to execute from your repository
     *
     * This is the file I want to execute in my
     * current git repository and I want to execute
     * this file every hour hence the Cron_Hourly
     */
    public $path = 'cron.php';

    /**
     * The arguments to pass
     *
     * This is a list of arguments to pass ot the
     * cronjob script I want to execute.
     *
     * This variable has two main formats. Either
     * you pass a simple list or you can pass an
     * associated array.
     *
     * Imagine you want to execute:
     * php crons/example.php -a A -b B
     *
     * Then the args will be:
     * $args = array('-a' => 'A', '-b' => 'B')
     *
     * If you want to execute
     * php crons/example.php one foo bar
     *
     * Then the $args will look like:
     * $args = array('one', 'foo', 'bar')
     */
    public $args = array();
}