<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace Scaffold;


use Scaffold\Entity\Config;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\ValueGenerator;
use Zend\Stdlib\ArrayUtils;

class ConfigWriter extends AbstractWriter
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $moduleConfig = [];

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->open();
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        return 'module/' . ucfirst($this->config->getModule()) . '/config/service.config.php';
    }

    /**
     * Open config
     */
    protected function open()
    {
        $path = $this->getPath();

        if (file_exists($path)) {
            $this->moduleConfig = include $path;
        }
    }

    /**
     * @param array $config
     */
    public function merge($config)
    {
        $this->moduleConfig = ArrayUtils::merge($this->moduleConfig, $config);
    }

    /**
     * @param OutputInterface $output
     */
    public function save(OutputInterface $output)
    {
        $config = new ValueGenerator($this->moduleConfig);
        $config->setOutputMode($config::OUTPUT_MULTIPLE_LINE);

        $data = '<?php' . PHP_EOL . PHP_EOL . 'return ' . $config->generate() . ';';

        $this->writeData($this->getPath(), $data, $output);
    }

}