<?php


namespace Core\Services;

use Authenticate\Grant\Exception\GrantException;
use Authenticate\SessionUser\SessionUser;
use Bat\SessionTool;
use Bat\UriTool;
use Core\Services\Exception\CoreServicesException;
use Kamille\Architecture\ApplicationParameters\ApplicationParameters;
use Kamille\Ling\Z;
use Kamille\Services\XConfig;
use Kamille\Services\XLog;
use Kamille\Utils\Routsy\LinkGenerator\ApplicationLinkGenerator;
use Kamille\Utils\Routsy\LinkGenerator\LinkGeneratorInterface;
use Logger\Logger;
use OnTheFlyForm\Provider\OnTheFlyFormProviderInterface;
use PersistentRowCollection\Finder\PersistentRowCollectionFinderInterface;
use PersistentRowCollection\InteractivePersistentRowCollectionInterface;
use TabathaCache\Cache\TabathaCacheInterface;


/**
 * This class contains shortcuts to modules services,
 * and to modules related functions.
 *
 */
class A
{


    /**
     * @param $formId
     * @return \OnTheFlyForm\OnTheFlyFormInterface
     * @throws CoreServicesException
     */
    public static function getOnTheFlyForm($formId)
    {
        $p = explode(':', $formId, 2);
        if (2 === count($p)) {
            /**
             * @var $provider OnTheFlyFormProviderInterface
             */
            $provider = X::get("Core_OnTheFlyFormProvider");
            return $provider->getForm($p[0], $p[1]);
        } else {
            throw new CoreServicesException("formId must contain a colon character");
        }
    }


    /**
     * When you log an exception, you can use this method to alter the form of the exception: --whether or
     * not to show the trace--
     *
     */
    public static function exceptionToString(\Exception $e)
    {
        $trace = XConfig::get("Core.showExceptionTrace", false);
        if (true === $trace) {
            return (string)$e;
        }
        $s = (string)$e;
        $p = explode(PHP_EOL, $s, 2);
        return $p[0];
    }


    public static function has($badge, $throwEx = false)
    {
        if (null !== ($grantor = X::get("Authenticate_grantor", null, false))) {
            /**
             * @var $grantor \Authenticate\Grant\GrantorInterface
             */
            if (true === $grantor->has($badge)) {
                return true;
            }
        }
        if (true === $throwEx) {
            throw new GrantException("You don't have the necessary privilege to do this action ($badge)");
        }
        return false;
    }


    public static function link($routeId, array $params = [], $absolute = false, $https = null)
    {
        /**
         * @var $linkGen LinkGeneratorInterface
         */
        $linkGen = X::get('Core_LinkGenerator');
        $ret = $linkGen->getUri($routeId, $params);
        if (true === $absolute) {
            if (null === $https) {
                $https = ("http" === XConfig::get("Core.defaultProtocol")) ? false : true;
            }
            $protocol = (true === $https) ? 'https' : 'http';
            $host = UriTool::getHost();
            $ret = $protocol . "://" . $host . $ret;
        }
        return $ret;
    }

    public static function addBodyEndJsCode($groupId, $code)
    {
        if (null !== ($coll = X::get("Core_lazyJsInit", null, false))) {
            /**
             * @var $coll \Module\Core\JsLazyCodeCollector\JsLazyCodeCollectorInterface
             */
            return $coll->addJsCode($groupId, $code);
        }
        return false;
    }

    public static function quickPdoInit()
    {
        if (null !== ($obj = X::get("Core_QuickPdoInitializer", null, false))) {
            /**
             * @var $obj \Module\Core\Pdo\QuickPdoInitializer
             */
            $obj->init();
            return true;
        }
        return false;
    }


    /**
     * When you create test scripts in kamille, you can initialize the logger system
     * with this method.
     */
    public static function loggerInit()
    {
        $logger = Logger::create();
        Hooks::call("Core_addLoggerListener", $logger);
        XLog::setLogger($logger); // now XLog is initialized for the rest of the application :)
    }

    /**
     * @return InteractivePersistentRowCollectionInterface|false
     * @throws \Exception
     */
    public static function getPrc($prcId, $checkInteractive = true, $throwEx = true)
    {
        /**
         * @var $finder PersistentRowCollectionFinderInterface
         */
        $finder = X::get("Core_PersistentRowCollectionFinder");
        if (false !== ($prc = $finder->find($prcId))) {
            if (false === $checkInteractive) {
                return $prc;
            } else {
                if ($prc instanceof InteractivePersistentRowCollectionInterface) {
                    return $prc;
                } else {
                    $msg = "Prc class not an instance of InteractivePersistentRowCollectionInterface, prcId=$prcId";
                    if (true === ApplicationParameters::get("debug")) {
                        XLog::error($msg);
                    }
                    if (true === $throwEx) {
                        throw new \Exception($msg);
                    }
                    return false;
                }
            }

        }
        $msg = "Prc not found with prcId=$prcId";
        if (true === ApplicationParameters::get("debug")) {
            XLog::error($msg);
        }
        if (true === $throwEx) {
            throw new \Exception($msg);
        }
        return false;
    }


    public static function prcLink($prcId, $type = "list")
    {
        return Z::link("NullosAdmin_crud") . "?type=$type&prc=$prcId";
    }

    /**
     * @return TabathaCacheInterface
     */
    public static function cache()
    {
        return X::get("Core_TabathaCache");
    }

    /**
     * Call this method to quickly boot the app environment for your test scripts
     */
    public static function testInit()
    {
        // session
        SessionTool::start();
        SessionUser::$key = 'frontUser';


        // logger
        A::loggerInit();
        $logger = Logger::create();
        Hooks::call("Core_addLoggerListener", $logger);
        XLog::setLogger($logger);


        // http
        if ('cli' === php_sapi_name()) {
            ApplicationParameters::set('request', \Kamille\Architecture\Request\Web\FakeHttpRequest::create()->setHost("lee"));
        } else {
            ApplicationParameters::set('request', \Kamille\Architecture\Request\Web\HttpRequest::create());
        }

        // pdo
        A::quickPdoInit();
    }
}