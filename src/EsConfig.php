<?php

namespace EsConfig;

class EsConfig
{
    const VERSION             = '2.2.1';
    const CONFIG_FILE         = '.esconfig.json';
    const ENVIRONMENT_FILE    = '.esconfig.environment';
    const DEFAULT_ENVIRONMENT = 'DEVELOPMENT';

    // --------------------------------------------------------------------------

    /**
     * The version of ElasticSearch being queried
     *
     * @var null
     */
    private static $sVersion = null;

    // --------------------------------------------------------------------------

    /**
     * Runs the command
     *
     * @param array $aArgs The arguments passed to the script
     *
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
        $sMethod = preg_replace_callback(
            '/\-([a-z])/',
            function ($aInput) {
                return strtoupper($aInput[1]);
            },
            $sMethod
        );

        if (method_exists(get_class(), $sMethod)) {
            self::{$sMethod}($aArgs);
        } else {
            self::help();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Write text to the console
     *
     * @param string $sText the text to write
     *
     * @return void
     */
    protected static function write($sText = '')
    {
        echo $sText;
    }

    // --------------------------------------------------------------------------

    /**
     * Write text to the console and move to a new line
     *
     * @param string $sText The text to write
     *
     * @return void
     */
    protected static function writeLn($sText = '')
    {
        self::write($sText . "\n");
    }

    // --------------------------------------------------------------------------

    /**
     * Outputs some help information
     *
     * @return void
     */
    protected static function help()
    {
        self::writeLn('This tool makes it simple to manage a basic elastic search setup.');
        self::writeLn('Through the use of a json file easily create index mappings, settings');
        self::writeLn('and populate with data.');
        self::writeLn();
        self::writeLn('Available Commands:');
        self::writeLn();
        self::writeLn('nuke         Destroy all data in the cluster');
        self::writeLn('reset        Delete all indexes and recreate them with mappings');
        self::writeLn('reset-ingest Delete all ingest pipelines and recreate them');
        self::writeLn('warm         Index all the content');
        self::writeLn();
    }

    // --------------------------------------------------------------------------

    /**
     * Executes a request to the server
     *
     * @param string $sMethod The type of request
     * @param string $sUrl    The URL of the request
     * @param array  $aData   Any data to send with the request (as JSON)
     *
     * @return \stdClass
     */
    protected static function request($sMethod, $sUrl, $aData = [])
    {
        //  Encode the data as JSON tos end to the server
        $sData = json_encode($aData);

        //  Start cURL
        $oCh = curl_init();

        curl_setopt($oCh, CURLOPT_URL, $sUrl);
        curl_setopt($oCh, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($oCh, CURLOPT_RETURNTRANSFER, true);

        if (!empty($sData)) {
            curl_setopt($oCh, CURLOPT_POSTFIELDS, $sData);
            curl_setopt(
                $oCh,
                CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($sData),
                ]
            );
        }

        $sResponse = curl_exec($oCh);
        curl_close($oCh);

        return json_decode($sResponse);
    }

    // --------------------------------------------------------------------------

    /**
     * Execute a GET request
     *
     * @param string $sUrl The URL to GET
     *
     * @return \stdClass
     */
    protected static function get($sUrl)
    {
        return self::request('GET', $sUrl);
    }

    // --------------------------------------------------------------------------

    /**
     * Execute a POST request
     *
     * @param string $sUrl  The URL to POST to
     * @param array  $aData An array of data to POST
     *
     * @return \stdClass
     */
    protected static function post($sUrl, $aData = [])
    {
        return self::request('POST', $sUrl, $aData);
    }

    // --------------------------------------------------------------------------

    /**
     * Execute a PUT request
     *
     * @param string $sUrl  The URL to PUT to
     * @param array  $aData An array of data to PUT
     *
     * @return \stdClass
     */
    protected static function put($sUrl, $aData = [])
    {
        return self::request('PUT', $sUrl, $aData);
    }

    // --------------------------------------------------------------------------

    /**
     * Execute a DELETE request
     *
     * @param string $sUrl  The URL to DELETE to
     * @param array  $aData An array of data to DELETE
     *
     * @return \stdClass
     */
    protected static function delete($sUrl, $aData = [])
    {
        return self::request('DELETE', $sUrl, $aData);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the configuration file
     *
     * @return \stdClass
     * @throws \Exception
     */
    protected static function getConfig()
    {
        $sPath = getcwd() . DIRECTORY_SEPARATOR . self::CONFIG_FILE;

        if (file_exists($sPath)) {

            $sConfig = file_get_contents($sPath);
            $oConfig = json_decode($sConfig);

            if (empty($oConfig)) {
                throw new \Exception('Invalid config file [' . $sPath . '].', 1);
            }

            return $oConfig;

        } else {

            throw new \Exception('Could not find config file [' . $sPath . '].', 1);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the current environment
     *
     * @param array $aArgs The arguments passed to the script
     *
     * @return string
     */
    protected static function getEnvironment($aArgs)
    {
        $oConfig  = self::getConfig();
        $sEnvPath = getcwd() . DIRECTORY_SEPARATOR . self::ENVIRONMENT_FILE;

        if (file_exists($sEnvPath)) {
            $sEnvFile = strtoupper(trim(file_get_contents($sEnvPath)));
        }

        //  Explicitly set
        if (!empty($aArgs[2])) {

            return strtoupper($aArgs[2]);

            // .esconfig.environment file
        } elseif (!empty($sEnvFile)) {

            return $sEnvFile;

            //  Default environment from .esconfig.json
        } elseif (!empty($oConfig->default_environment)) {

            return strtoupper($oConfig->default_environment);

            //  Default environment
        } else {

            return self::DEFAULT_ENVIRONMENT;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Nuke the cluster
     *
     * @param array $aArgs The arguments passed to the script
     *
     * @return void
     */
    protected static function nuke($aArgs)
    {
        self::writeLn('[Nuke 💣]');
        self::writeLn();

        try {

            $oConfig = self::getConfig();
            $sEnv    = self::getEnvironment($aArgs);

            self::writeLn('Detected environment: ' . $sEnv);

            if (empty($oConfig->host->{$sEnv})) {
                throw new \Exception('No host defined for environment "' . $sEnv . '"', 1);
            }

            $sUrl      = $oConfig->host->{$sEnv} . '/';
            $oResponse = self::delete($sUrl . '_all');

            if (!empty($oResponse->error)) {
                self::writeln('Failed: ' . $oResponse->error->type . ': ' . $oResponse->error->reason);
            } else {
                self::writeln('Success');
            }
            self::writeLn();

        } catch (\Exception $e) {

            self::writeLn('[ERROR: ' . $e->getMessage() . ']');
            self::writeLn();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Reset the cluster
     *
     * @param array $aArgs The arguments passed to the script
     *
     * @return void
     */
    protected static function reset($aArgs)
    {
        self::writeLn('[Reset]');
        self::writeLn();

        try {

            $oConfig = self::getConfig();
            $sEnv    = self::getEnvironment($aArgs);

            self::writeLn('Detected environment: ' . $sEnv);

            if (empty($oConfig->host->{$sEnv})) {
                throw new \Exception('No host defined for environment "' . $sEnv . '"', 1);
            }

            $sUrl = $oConfig->host->{$sEnv} . '/';
            static::detectClientVersion($sUrl);

            foreach ($oConfig->indexes as $oIndex) {

                self::writeLn('Deleting index [' . $oIndex->name . ']');
                $oResponse = self::delete($sUrl . $oIndex->name);
                if (!empty($oResponse->error)) {
                    self::writeln('Failed: ' . $oResponse->error->type . ': ' . $oResponse->error->reason);
                } else {
                    self::writeln('Success');
                }

                self::writeLn('Creating  index [' . $oIndex->name . ']');

                $oSettings = !empty($oIndex->settings) ? $oIndex->settings : (object) [];
                $oMappings = !empty($oIndex->mappings) ? $oIndex->mappings : (object) [];

                /**
                 * The HTTP method type for setting mappings was changed in version 5.0 from POST to PUT
                 */
                if (version_compare(static::$sVersion, '5.0.0')) {
                    $oResponse = self::put(
                        $sUrl . $oIndex->name,
                        [
                            'settings' => $oSettings,
                            'mappings' => $oMappings,
                        ]
                    );
                } else {
                    $oResponse = self::post(
                        $sUrl . $oIndex->name,
                        [
                            'settings' => $oSettings,
                            'mappings' => $oMappings,
                        ]
                    );
                }

                if (!empty($oResponse->error)) {
                    self::writeln('Failed: ' . $oResponse->error->type . ': ' . $oResponse->error->reason);
                } else {
                    self::writeln('Success');
                }

                self::writeLn();
            }

        } catch (\Exception $e) {

            self::writeLn('[ERROR: ' . $e->getMessage() . ']');
            self::writeLn();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Resets _ingest configurations
     *
     * @param $aArgs
     */
    protected static function resetIngest($aArgs)
    {
        self::writeLn('[Reset Ingest]');
        self::writeLn();

        try {

            $oConfig = self::getConfig();
            $sEnv    = self::getEnvironment($aArgs);

            self::writeLn('Detected environment: ' . $sEnv);

            if (empty($oConfig->host->{$sEnv})) {
                throw new \Exception('No host defined for environment "' . $sEnv . '"', 1);
            }

            $sUrl = $oConfig->host->{$sEnv} . '/';
            static::detectClientVersion($sUrl);

            //  Pipelines
            if (!empty($oConfig->_ingest->pipelines)) {
                foreach ($oConfig->_ingest->pipelines as $oPipeline) {

                    self::writeLn('Deleting pipeline [' . $oPipeline->name . ']');
                    $oResponse = self::delete(
                        $sUrl . '_ingest/pipeline/' . $oPipeline->name
                    );
                    if (!empty($oResponse->error)) {
                        self::writeln('Failed: ' . $oResponse->error->type . ': ' . $oResponse->error->reason);
                    } else {
                        self::writeln('Success');
                    }

                    self::writeLn('Creating pipeline [' . $oPipeline->name . ']');
                    $oResponse = self::put(
                        $sUrl . '_ingest/pipeline/' . $oPipeline->name,
                        $oPipeline->body
                    );

                    if (!empty($oResponse->error)) {
                        self::writeln('Failed: ' . $oResponse->error->type . ': ' . $oResponse->error->reason);
                    } else {
                        self::writeln('Success');
                    }

                    self::writeLn();
                }
            } else {
                self::writeLn('No pipelines to configure');
                self::writeLn();
            }

        } catch (\Exception $e) {

            self::writeLn('[ERROR: ' . $e->getMessage() . ']');
            self::writeLn();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * WArm the cluster
     *
     * @param array $aArgs The arguments passed to the script
     *
     * @return void
     */
    protected static function warm($aArgs)
    {
        self::writeLn('[Warm]');
        self::writeLn();

        try {

            $oConfig = self::getConfig();
            $sEnv    = self::getEnvironment($aArgs);

            self::writeLn('Detected environment: ' . $sEnv);

            if (empty($oConfig->host->{$sEnv})) {
                throw new \Exception('No host defined for environment "' . $sEnv . '"', 1);
            }

            if (empty($oConfig->warm->{$sEnv})) {
                throw new \Exception('No warm up defined for environment "' . $sEnv . '"', 1);
            }

            //  Parse in special variables
            $sCommand = $oConfig->warm->{$sEnv};
            $sCommand = preg_replace('/\{\{__HOST__\}\}/', $oConfig->host->{$sEnv}, $sCommand);

            self::writeLn('Executing command: ' . $sCommand);

            $aDescriptorSpec = [
                0 => ["pipe", "r"],  // stdin is a pipe that the child will read from
                1 => ["pipe", "w"],  // stdout is a pipe that the child will write to
                2 => ["file", "/tmp/error-output.txt", "a"] // stderr is a file to write to
            ];

            $process = proc_open(
                $sCommand,
                $aDescriptorSpec,
                $aPipes,
                getcwd()
            );

            if (is_resource($process)) {

                // $aPipes now looks like this:
                // 0 => writeable handle connected to child stdin
                // 1 => readable handle connected to child stdout
                // Any error output will be appended to /tmp/error-output.txt

                $sOutput = stream_get_contents($aPipes[1]);
                $aOutput = explode("\n", $sOutput);

                foreach ($aOutput as $sLine) {
                    self::writeLn($sLine);
                }

                fclose($aPipes[1]);

                // It is important that you close any pipes before calling
                // proc_close in order to avoid a deadlock
                proc_close($process);
            }

        } catch (\Exception $e) {

            self::writeLn('[ERROR: ' . $e->getMessage() . ']');
            self::writeLn();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Detects the version of the ElasticSearch instance being queried
     *
     * @param string $sUrl The URL to the instance
     *
     * @throws \Exception
     */
    private static function detectClientVersion($sUrl)
    {
        $oResponse = static::get($sUrl);
        if (empty($oResponse)) {
            throw new \Exception('Failed to query ElasticSearch for version details');
        }

        static::$sVersion = $oResponse->version->number;
    }
}
