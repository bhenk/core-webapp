<?php

namespace unit\corewa\logging\build;

use bhenk\corewa\conf\Config;
use bhenk\corewa\logging\build\LoggerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Processor\IntrospectionProcessor;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use function end;
use function get_class;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertStringStartsWith;
use function PHPUnit\Framework\assertTrue;

class LoggerBuilderTest extends TestCase {

    private string $application_root;
    private string $config_file;

    public function setUp(): void {
        $this->application_root = Config::get()->getApplicationRoot();
        $this->config_file = Config::get()->getConfigFile();
        LoggerBuilder::get()->reset();
    }

    public function tearDown(): void {
        Config::load($this->application_root, $this->config_file);
        LoggerBuilder::get()->reset();
    }

    public function testBuildWithLoadError() {
        $config = ["logger_definition_file" => "no_definition.xyz"];
        Config::get()->setConfigurationFor("bhenk\corewa\logging\build\LoggerBuilder", $config);
        $builder = LoggerBuilder::get();
        //
        $builder->setQuiet(true);
        $logger = $builder->build("not of interest");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertNotEmpty($warnings);
        assertStringStartsWith("File does not exists", $warnings[0]);
        assertStringStartsWith("Could not create logger.", end($warnings));
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildWithEntryError() {
        $builder = LoggerBuilder::get();
        //
        $builder->setQuiet(true);
        $logger = $builder->build("foobar");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertNotEmpty($warnings);
        assertStringStartsWith("Entry 'foobar' not found.", $warnings[0]);
        assertStringStartsWith("Could not create logger.", end($warnings));
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildWithUnknownKeyError() {
        $entry = [
            "wrong wrong" => []
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("fooLogger", $entry);
        //
        $builder->setQuiet(true);
        $logger = $builder->build("fooLogger");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertNotEmpty($warnings);
        assertStringStartsWith("Unknown key: 'wrong wrong'.", $warnings[0]);
        assertStringStartsWith("Could not create logger.", end($warnings));
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildWithNoHandlers() {
        $entry = [
            "definition" => [
                "channel" => "test",
            ]
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("emptyDef", $entry);
        //
        $builder->setQuiet(true);
        $logger = $builder->build("emptyDef");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertNotEmpty($warnings);
        assertStringStartsWith("No handlers set for logger 'emptyDef'", $warnings[0]);
        assertStringStartsWith("Could not create logger.", end($warnings));
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildWithMissingClassName() {
        $entry = [
            "definition" => [
                "channel" => "chan",
                "handlers" => [
                    "handler01" => [

                    ]
                ]
            ]
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("noClass", $entry);
        //
        $builder->setQuiet(true);
        $logger = $builder->build("noClass");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertNotEmpty($warnings);
        assertStringStartsWith("No 'class_name' set on handler 'handler01' from entry 'noClass'", $warnings[0]);
        assertStringStartsWith("Could not create logger.", end($warnings));
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildSimpelHandlerWithSuccess() {
        $entry = [
            "definition" => [
                "channel" => "chan",
                "handlers" => [
                    "handler01" => [
                        "class_name" => "Monolog\Handler\FirePHPHandler",
                    ]
                ]
            ]
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("success", $entry);
        //
        $builder->setQuiet(false);
        $logger = $builder->build("success");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertEquals("Monolog\Handler\FirePHPHandler", get_class($logger->getHandlers()[0]));
        assertEmpty($warnings);
    }

    public function testBuildHandlerWithStream() {
        $entry = [
            "definition" => [
                "channel" => "chan",
                "handlers" => [
                    "handler01" => [
                        "class_name" => "Monolog\Handler\StreamHandler",
                        "paras" => [
                            "stream" => "php://stdout",
                            "level" => Level::Debug,
                            "bubble" => true,
                            "filePermission" => null,
                            "useLocking" => false,
                        ],
                    ]
                ]
            ]
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("withStream", $entry);
        //
        $builder->setQuiet(false);
        $logger = $builder->build("withStream");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertEquals("Monolog\Handler\StreamHandler", get_class($logger->getHandlers()[0]));
        assertEmpty($warnings);
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildWithFormatter() {
        $entry = [
            "definition" => [
                "channel" => "chan",
                "handlers" => [
                    "handler01" => [
                        "class_name" => "Monolog\Handler\StreamHandler",
                        "paras" => [
                            "stream" => "php://stdout",
                            "level" => Level::Debug,
                            "bubble" => true,
                            "filePermission" => null,
                            "useLocking" => false,
                        ],
                        "formatter" => [
                            "class_name" => "Monolog\Formatter\LineFormatter",
                            "paras" => [
                                "format" => "%level_name% | %datetime% > %message% | %context% %extra%\n",
                                "dateFormat" => "H:i:s:u",
                                "allowInlineLineBreaks" => false,
                                "ignoreEmptyContextAndExtra" => false,
                                "includeStacktraces" => false
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("withFormat", $entry);
        //
        $builder->setQuiet(false);
        $logger = $builder->build("withFormat");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        /** @var StreamHandler $handler */
        $handler = $logger->getHandlers()[0];
        assertEquals("Monolog\Handler\StreamHandler", get_class($handler));
        assertEquals("Monolog\Formatter\LineFormatter", get_class($handler->getFormatter()));
        assertEmpty($warnings);
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildWithProcessor() {
        $entry = [
            "definition" => [
                "channel" => "chan",
                "handlers" => [
                    "handler01" => [
                        "class_name" => "Monolog\Handler\StreamHandler",
                        "paras" => [
                            "stream" => "php://stdout",
                            "level" => Level::Debug,
                            "bubble" => true,
                            "filePermission" => null,
                            "useLocking" => false,
                        ],
                    ],
                ],
                "processors" => [
                    "processor01" => [
                        "class_name" => "Monolog\Processor\IntrospectionProcessor",
                        "paras" => [
                            "level" => Level::Error,
                            "skipClassesPartials" => [],
                            "skipStackFramesCount" => 0,
                        ],
                    ],
                ],
            ],
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("withProcessor", $entry);
        //
        $builder->setQuiet(false);
        $logger = $builder->build("withProcessor");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        /** @var IntrospectionProcessor $processorr */
        $processor = $logger->getProcessors()[0];
        assertEquals("Monolog\Processor\IntrospectionProcessor", get_class($processor));
        assertEmpty($warnings);
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
        //$logger->error(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildWithCreator() {
        $entry = [
            "creator" => [
                "class_name" => "unit\corewa\logging\build\DummyCreator",
            ]
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("try_creator", $entry);
        //
        $builder->setQuiet(false);
        DummyCreator::reset();
        $logger = $builder->build("try_creator");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertEmpty($warnings);
        assertTrue(DummyCreator::wasCalled());
        assertEquals([], DummyCreator::getParas());
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildWithCreatorAndParas() {
        $entry = [
            "creator" => [
                "class_name" => "unit\corewa\logging\build\DummyCreator",
                "paras" => [
                    "foo" => false,
                    "word" => "reflection",
                    "space" => []
                ]
            ]
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("try_paras", $entry);
        //
        $builder->setQuiet(false);
        DummyCreator::reset();
        $logger = $builder->build("try_paras");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertEmpty($warnings);
        assertTrue(DummyCreator::wasCalled());
        $expected = [
            "foo" => false,
            "word" => "reflection",
            "space" => []
        ];
        assertEquals($expected, DummyCreator::getParas());
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

    public function testBuildWithRequestLoggerCreator() {
        $entry = [
            "creator" => [
                "class_name" => "bhenk\corewa\logging\build\RequestLoggerCreator",
                "paras" => [
                    "channel" => "test_req",
                    "level" => Level::Info,
                    "filename" => "logs/unit/req.log",
                    "filename_format" => "{filename}-{date}",
                    "filename_date_format" => "Y-m",
                    "format" => "%datetime% %extra%\n"
                ],
            ],
        ];
        $builder = LoggerBuilder::get();
        $builder->addEntry("req", $entry);
        //
        $builder->setQuiet(false);
        $logger = $builder->build("req");
        $warnings = $builder->getWarnings();

        assertInstanceOf(LoggerInterface::class, $logger);
        assertEmpty($warnings);
        //$logger->notice(get_class($this) . "->" . __FUNCTION__);
    }

}
