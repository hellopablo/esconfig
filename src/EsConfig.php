<?php

namespace EsConfig;

class EsConfig
{
    const VERSION = '1.0.0';
    const CONFIG_FILE = '~/.anonymiserc';

    // --------------------------------------------------------------------------

    /**
     * Runs the command
     * @param  array $aArgs The arguments passed to the script
     * @return void
     */
    public static function go($aArgs)
    {
        self::writeLn();
        self::writeLn('+----------------------------+');
        self::writeLn('| ElasticSearch Configurator |');
        self::writeLn('| v' . self::VERSION . '                     |');
        self::writeLn('+----------------------------+');
        self::writeLn();

        $sMethod = !empty($aArgs[1]) ? $aArgs[1] : 'help';
        if (method_exists(get_class(), $sMethod)) {
            self::{$sMethod}($aArgs);
        } else {
            self::help();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Write text to the console
     * @param  string $sText the text to write
     * @return void
     */
    protected static function write($sText = '')
    {
        echo $sText;
    }

    // --------------------------------------------------------------------------

    /**
     * Write text to the console and move to a new line
     * @param  string $sText The text to write
     * @return void
     */
    protected static function writeLn($sText = '')
    {
        self::write($sText . "\n");
    }

    // --------------------------------------------------------------------------

    /**
     * Outputs some help information
     * @return void
     */
    protected static function help()
    {
        self::writeLn('This tool makes it simple to manage a basic elastic search setup.');
        self::writeLn('Through the use of a json file easily create index mappings and');
        self::writeLn('populate with data.');
        self::writeLn();
        self::writeLn('Available Commands:');
        self::writeLn();
        self::writeLn('nuke      Destroy all data in the cluster');
        self::writeLn('reset     Delete all indexes and recreate them with mappings');
        self::writeLn('warm      Index all the content');
        self::writeLn();
    }

    // --------------------------------------------------------------------------

    protected static function getConfig()
    {

    }

    // --------------------------------------------------------------------------

    /**
     * Nuke the cluster
     * @param  array $aArgs The arguments passed to the script
     * @return vpid
     */
    protected static function nuke($aArgs)
    {
        self::writeLn('[Nuke 💣]');
        self::writeLn();

        $oConfig = self::getConfig();
        if (empty($oConfig)) {
            self::writeLn('Could not find .esconfig.json file');
            self::writeLn();
            return;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Reset the cluster
     * @param  array $aArgs The arguments passed to the script
     * @return vpid
     */
    protected static function reset($aArgs)
    {
        self::writeLn('[Reset]');
        self::writeLn();

        $oConfig = self::getConfig();
        if (empty($oConfig)) {
            self::writeLn('Could not find .esconfig.json file');
            self::writeLn();
            return;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * WArm the cluster
     * @param  array $aArgs The arguments passed to the script
     * @return vpid
     */
    protected static function warm($aArgs)
    {
        self::writeLn('[Warm]');
        self::writeLn();

        $oConfig = self::getConfig();
        if (empty($oConfig)) {
            self::writeLn('Could not find .esconfig.json file');
            self::writeLn();
            return;
        }
    }
}
