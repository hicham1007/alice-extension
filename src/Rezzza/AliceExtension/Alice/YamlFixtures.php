<?php

namespace Rezzza\AliceExtension\Alice;

use Symfony\Component\Yaml\Yaml as YamlParser;

class YamlFixtures implements AliceFixtures
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * @inheritdoc
     */
    public function load()
    {
        return $this->loadYaml($this->file);
    }

    protected function loadYaml($file)
    {
        if (!is_file($file)) {
            return array();
        }

        // Copy from Nelmio\Alice\Loader\Yaml to just process yaml
        ob_start();
        $loader = $this;

        // isolates the file from current context variables and gives
        // it access to the $loader object to inline php blocks if needed
        $includeWrapper = function () use ($file, $loader) {
            return include $file;
        };
        $data = $includeWrapper();

        if (1 === $data) {
            // include didn't return data but included correctly, parse it as yaml
            $yaml = ob_get_clean();
            $data = YamlParser::parse($yaml);
        } else {
            // make sure to clean up if theres a failure
            ob_end_clean();
        }

        if (!is_array($data)) {
            throw new \UnexpectedValueException('Yaml files must parse to an array of data');
        }

        return $data;
    }
}
