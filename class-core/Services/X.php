<?php


namespace Core\Services;


use Kamille\Services\AbstractX;


/**
 *
 * Service container of the application.
 * It contains the services of the application.
 *
 * Services can be added manually or by automates.
 *
 *
 * Rules of thumb: you can add new methods, but NEVER REMOVE A METHOD
 * (because you might break a dependency that someone made to this method)
 *
 *
 * Note1: remember that this class belongs to the application,
 * so don't hesitate to use it how you like (use php constants if you want).
 * You would just throw it away and restart for a new application, no big deal.
 *
 *
 * Note2: please avoid use statements at the top of this file.
 * I have no particular arguments why, but it makes my head cleaner to
 * see a clean top of the file, thank you by advance, ling.
 *
 *
 */
class X extends AbstractX
{

    //--------------------------------------------
    // AUTHENTICATE
    //--------------------------------------------
    protected static function Authenticate_userStore()
    {
        $f = \Kamille\Services\XConfig::get("Authenticate.pathUserStore");
        return \Authenticate\UserStore\FileUserStore::create()->setFile($f);
    }

    protected static function Authenticate_badgeStore()
    {
        $f = \Kamille\Services\XConfig::get("Authenticate.pathBadgeStore");
        return \Authenticate\BadgeStore\FileBadgeStore::create()->setFile($f);
    }

    protected static function Authenticate_grantor()
    {
        $badgeStore = \Core\Services\X::get(\Kamille\Services\XConfig::get("Authenticate.serviceBadgeStore"));
        $grantor = \Authenticate\Grant\Grantor::create()->setBadgeStore($badgeStore);
        return $grantor;
    }


    //--------------------------------------------
    // CORE
    //--------------------------------------------
    protected static function Core_webApplicationHandler()
    {
        return new \Module\Core\ApplicationHandler\WebApplicationHandler();
    }

    protected static function Core_lawsUtil()
    {
        $layoutProxy = \Kamille\Mvc\LayoutProxy\LawsLayoutProxy::create();
        \Core\Services\Hooks::call("Core_addLawsUtilProxyDecorators", $layoutProxy);
        $util = \Kamille\Utils\Laws\LawsUtil::create()
            ->setLawsLayoutProxy($layoutProxy);
        \Core\Services\Hooks::call("Core_configureLawsUtil", $util);
        return $util;
    }

    protected static function Core_lazyJsInit()
    {
        $collector = \Module\Core\JsLazyCodeCollector\JsLazyCodeCollector::create();
        \Core\Services\Hooks::call("Core_lazyJsInit_addCodeWrapper", $collector);
        return $collector;
    }

    protected static function Core_QuickPdoInitializer()
    {
        $initializer = new \Module\Core\Pdo\QuickPdoInitializer();
        return $initializer;
    }


    protected static function Core_OnTheFlyFormProvider()
    {
        $provider = \OnTheFlyForm\Provider\OnTheFlyFormProvider::create();
        \Core\Services\Hooks::call("Core_feedOnTheFlyFormProvider", $provider);
        return $provider;
    }

    protected static function Core_PersistentRowCollectionFinder()
    {
        $initializer = new \Core\Framework\PersistentRowCollection\Finder\PersistentRowCollectionFinder();
        return $initializer;
    }


    protected static function Core_LawsViewRenderer()
    {
        $r = new \Module\Core\Utils\Laws\LawsViewRenderer();
        return $r;
    }


    protected static function Core_LinkGenerator()
    {
        /**
         * @var $routsyRouter \Kamille\Utils\Routsy\RoutsyRouter
         */
        $routsyRouter = \Core\Services\X::get("Core_RoutsyRouter");
        $routes = $routsyRouter->getRoutes();
        return \Kamille\Utils\Routsy\LinkGenerator\LinkGenerator::create()->setRoutes($routes);
    }


    protected static function Core_Localyser()
    {
        $o = \Localys\Localyser\Localyser::create();
        return $o;
    }

    protected static function Core_RoutsyRouter()
    {
        $routsyRouter = \Kamille\Utils\Routsy\RoutsyRouter::create();
        $routsyRouter
            ->addCollection(\Kamille\Utils\Routsy\RouteCollection\RoutsyRouteCollection::create()
                ->setFileName("routes")
                ->setOnRouteMatch(function ($routeId) {
                    \Kamille\Architecture\Registry\ApplicationRegistry::set("routsyRouteId", $routeId);
                })
            )
            ->addCollection(\Kamille\Utils\Routsy\RouteCollection\PrefixedRoutsyRouteCollection::create()
                ->setFileName("back")
                ->setOnRouteMatch(function ($routeId) {
                    \Kamille\Architecture\ApplicationParameters\ApplicationParameters::set("theme", \Kamille\Services\XConfig::get("Core.themeBack"));
                })
                ->setUrlPrefix(\Kamille\Services\XConfig::get("Core.uriPrefixBackoffice"))
            );
        \Core\Services\Hooks::call("Core_configureRoutsyRouter", $routsyRouter);
        return $routsyRouter;
    }

    protected static function Core_TabathaCache()
    {
        if (true === \Kamille\Architecture\ApplicationParameters\ApplicationParameters::get("debug")) {
            $r = new \Module\Core\Planets\TabathaCache\DebugTabathaCache();
        } else {
            $r = new \TabathaCache\Cache\TabathaCache();
        }

        $r->setDefaultForceGenerate((false === \Kamille\Services\XConfig::get("Core.enableTabathaCache")));

        $r->setDir(\Kamille\Architecture\ApplicationParameters\ApplicationParameters::get("app_dir") . "/cache/tabatha");
        return $r;
    }

    protected static function Core_umail()
    {
        return \Module\ThisApp\Umail\ThisAppUmail::create();
        return \Kamille\Utils\Umail\KamilleUmail::create();
    }

    protected static function Core_ListModifierCircle()
    {
        $c = new \ListModifier\Circle\ListModifierCircle();
        Hooks::call("Core_feedListModifierCircle", $c);
        return $c;
    }


    //--------------------------------------------
    // DATATABLE
    //--------------------------------------------
    public static function DataTable_profileFinder()
    {
        $appDir = \Kamille\Ling\Z::appDir();
        $f = \Module\DataTable\DataTableProfileFinder\DataTableProfileFinder::create();
        $f->setProfilesDir($appDir . "/config/datatable-profiles");
        \Core\Services\Hooks::call("DataTable_configureProfileFinder", $f);
        return $f;
    }


    //--------------------------------------------
    // EKOM
    //--------------------------------------------
    protected static function Ekom_CheckoutFormBuilder()
    {
        $o = new \StepFormBuilder\StepFormBuilder();
        \Core\Services\Hooks::call('Ekom_configureCheckoutFormBuilder', $o);
        return $o;
    }

    protected static function Ekom_CheckoutLayerProvider()
    {
        $o = new \Module\Ekom\CheckoutLayerProvider\CheckoutLayerProvider();
        \Core\Services\Hooks::call('Ekom_configureCheckoutLayerProvider', $o);
        return $o;
    }

    protected static function Ekom_getAttributesModelGeneratorFactory()
    {
        $c = new \Module\Ekom\ProductBox\AttributesModel\GeneratorFactory\EkomAttributesModelGeneratorFactory();
        \Core\Services\Hooks::call('Ekom_feedAttributesModelGeneratorFactory', $c);
        return $c;
    }

    protected static function Ekom_getCarrierCollection()
    {
        $c = \Module\Ekom\Carrier\Collection\CarrierCollection::create();
        \Core\Services\Hooks::call('Ekom_feedCarrierCollection', $c);
        return $c;
    }

    protected static function Ekom_getPaymentMethodHandlerCollection()
    {
        $c = \Module\Ekom\PaymentMethodHandler\Collection\PaymentMethodHandlerCollection::create();
        \Core\Services\Hooks::call('Ekom_feedPaymentMethodHandlerCollection', $c);
        return $c;
    }


    protected static function Ekom_getProductPriceChain()
    {
        $c = \Module\Ekom\Price\PriceChain\EkomProductPriceChain::create();
        \Core\Services\Hooks::call('Ekom_feedEkomProductPriceChain', $c);
        return $c;
    }

    protected static function Ekom_getCartPriceChain()
    {
        $c = \Module\Ekom\Price\PriceChain\EkomCartPriceChain::create();
        \Core\Services\Hooks::call('Ekom_feedEkomCartPriceChain', $c);
        return $c;
    }

    protected static function Ekom_getTotalPriceChain()
    {
        $c = \Module\Ekom\Price\PriceChain\EkomTotalPriceChain::create();
        \Core\Services\Hooks::call('Ekom_feedEkomTotalPriceChain', $c);
        return $c;
    }


    protected static function Ekom_jsApiLoader()
    {
        $l = new \Module\Ekom\JsApiLoader\EkomJsApiLoader();
        \Core\Services\Hooks::call('Ekom_feedJsApiLoader', $l);
        return $l;
    }


    protected static function Ekom_ListBundleFactory()
    {
        $l = new \Module\Ekom\ListParams\ListBundleFactory\EkomListBundleFactory();
        \Core\Services\Hooks::call('Ekom_configureListBundle', $l);
        return $l;
    }


    protected static function Ekom_notifier()
    {
        $o = new \Module\Ekom\Notifier\EkomNotifier();
        \Core\Services\Hooks::call('Ekom_feedEkomNotifier', $o);
        return $o;
    }


    protected static function Ekom_OrderBuilderCollection()
    {
        $o = new \Module\Ekom\Utils\OrderBuilder\Collection\OrderBuilderCollection();
        \Core\Services\Hooks::call('Ekom_feedOrderBuilderCollection', $o);
        return $o;
    }

    protected static function Ekom_statusProviderCollection()
    {
        $o = new \Module\Ekom\Status\ProviderCollection\EkomStatusProviderCollection();
        \Core\Services\Hooks::call('Ekom_feedStatusProviderCollection', $o);
        return $o;
    }

    protected static function Ekom_ReferenceProvider()
    {
        $o = new \Module\Ekom\Utils\ReferenceProvider();
        \Core\Services\Hooks::call('Ekom_configureReferenceProvider', $o);
        return $o;
    }


    protected static function Ekom_statusProvider()
    {
        /**
         * @var \Module\Ekom\Status\ProviderCollection\EkomStatusProviderCollection $coll
         */
        $coll = \Core\Services\X::get("Ekom_statusProviderCollection");
        $all = $coll->all();
        $key = \Module\Ekom\Utils\E::conf("statusProvider");
        if (array_key_exists($key, $all)) {
            return $all[$key];
        }
        throw new \Module\Ekom\Exception\EkomException("statusProvider not configured for the current shop");
    }


    protected static function Ekom_productSearch()
    {

        return \Module\EkomFastSearch\ProductSearch\FastProductSearch::create();
//        return \Module\Ekom\ProductSearch\HeavyProductSearch::create();
//        return \Module\Ekom\ProductSearch\ProductSearch::create();
    }

    protected static function Ekom_dynamicWidgetBinder()
    {
        $o = \Kamille\Utils\Laws\DynamicWidgetBinder\DynamicWidgetBinder::create();
        \Core\Services\Hooks::call("Ekom_feedDynamicWidgetBinder", $o);
        return $o;
    }

//    protected static function Ekom_productIdToUniqueProductId()
//    {
//        $o = new \Module\Ekom\Utils\ProductIdToUniqueProductIdAdaptor\ProductIdToUniqueProductIdAdaptor();
//        \Core\Services\Hooks::call("Ekom_decorateProductIdToUniqueProductIdAdaptor", $o);
//        return $o;
//    }


    protected static function Ekom_OnTheFlyFormValidator()
    {
        $o = \FormTools\Validation\OnTheFlyFormValidator::create();
        $message = \Kamille\Services\XConfig::get("Ekom.OnTheFlyFormValidatorMessageClass", null);
        if (null !== $message) {
            $o->setMessage($message);
        }
        return $o;
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    protected static function EkomUserProductHistory_UserProductHistory()
    {

        $o = new \Module\EkomUserProductHistory\UserProductHistory\FileSystemUserProductHistory();
        $appDir = \Kamille\Architecture\ApplicationParameters\ApplicationParameters::get("app_dir");
        $o->setStatsDir("$appDir/data/EkomUserProductHistory");
        return $o;

    }

    //--------------------------------------------
    // INGENICO
    //--------------------------------------------
    protected static function PeiPei_IngenicoHandler()
    {
        $conf = \Kamille\Services\XConfig::get('PeiPei.ingenico.config');
        $h = new \Ingenico\Handler\IngenicoHandler();
        $c = new \Ingenico\Config\IngenicoConfig($conf);
        $h->setConfig($c);
        return $h;
    }

    //--------------------------------------------
    // NULLOS
    //--------------------------------------------
    protected static function NullosAdmin_themeHelper()
    {
        return \Module\NullosAdmin\ThemeHelper\ThemeHelper::inst();
    }




    //--------------------------------------------
    // UPLOAD PROFILE
    //--------------------------------------------
    protected static function UploadProfile_profileFinder()
    {
        $appDir = \Kamille\Architecture\ApplicationParameters\ApplicationParameters::get("app_dir");
        $finder = \Module\UploadProfile\ProfileFinder\FileProfileFinder::create()->setProfilesDir($appDir . "/config/upload-profiles");
        return $finder;
    }
}