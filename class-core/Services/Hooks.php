<?php


namespace Core\Services;

use ArrayToString\ArrayToStringTool;
use Kamille\Architecture\Registry\ApplicationRegistry;
use Kamille\Services\AbstractHooks;
use Kamille\Services\XLog;
use Module\Ekom\Api\EkomApi;

use Module\Ekom\Session\EkomSession;


/**
 * This class is used to hook modules dynamically.
 * This class is written by modules, so, be careful I guess.
 *
 * A hook is always a public static method (in this class)
 *
 *
 * Rules of thumb: you can add new methods, but NEVER REMOVE A METHOD
 * (because you might break a dependency that someone made to this method)
 */
class Hooks extends AbstractHooks
{
    //--------------------------------------------
    // EKOM
    //--------------------------------------------
    protected static function Ekom_adaptContextualConfig(array &$conf)
    {
    }


    protected static function Ekom_cart_addItemBefore(array &$hookParams)
    {

    }

    protected static function Ekom_CategoryController_decorateItemsGeneratorAndClaws(
        \QueryFilterBox\ItemsGenerator\ItemsGenerator $generator,
        \Kamille\Utils\Claws\Claws $claws,
        array $context)
    {
        // mit-start:EkomEvents
        \Module\EkomEvents\Helper\HooksHelper::Ekom_CategoryController_decorateItemsGenerator($generator, $claws, $context);
        // mit-end:EkomEvents

        // mit-start:EkomTrainingProducts
        \Module\EkomTrainingProducts\Helper\HooksHelper::Ekom_CategoryController_decorateItemsGenerator($generator, $claws, $context);
        // mit-end:EkomTrainingProducts
    }


    protected static function Ekom_categoryLayer_overrideLinkOptions(array &$linkOptions, $marker)
    {
        // mit-start:ThisApp
        \Module\ThisApp\Ekom\Helper\HooksHelper::Ekom_categoryLayer_overrideLinkOptions($linkOptions, $marker);
        // mit-end:ThisApp
    }


    protected static function Ekom_configureCheckoutFormBuilder(\StepFormBuilder\StepFormBuilder $builder)
    {

        // mit-start:ThisApp
//        $builder->registerStep('shipping', \Module\ThisApp\StepFormBuilder\Step\ShippingStep::create());
//        $builder->registerStep('payment', \Module\ThisApp\StepFormBuilder\Step\PaymentStep::create());


//        $builder->registerStep('login', \StepFormBuilder\Step\OnTheFlyFormStep::create()->setForm(\OnTheFlyForm\OnTheFlyForm::create([
//            "login",
//        ], 'login-key')
//            ->setValidationRules([
//                'login' => ['required'],
//            ])
//        ));
        $builder->registerStep('login', \Module\Ekom\StepFormBuilder\Step\LoginStep::create());


        $builder->registerStep('training1', \StepFormBuilder\Step\OnTheFlyFormStep::create()->setForm(\OnTheFlyForm\OnTheFlyForm::create([
            "motivation",
        ], 'motivation-key')
            ->setValidationRules([
                'motivation' => ['required'],
            ])
        ));
        $builder->registerStep('training2', \StepFormBuilder\Step\OnTheFlyFormStep::create()->setForm(\OnTheFlyForm\OnTheFlyForm::create([
            "provenance",
        ], 'provenance-key')
            ->setValidationRules([
                'provenance' => ['required'],
            ])
        ));
        $builder->registerStep('training3', \StepFormBuilder\Step\OnTheFlyFormStep::create()->setForm(\OnTheFlyForm\OnTheFlyForm::create([
            "explanations",
        ], 'explanations-key')
            ->setValidationRules([
                'explanations' => ['required'],
            ])
        ));


        $builder->registerStep('shipping', \StepFormBuilder\Step\OnTheFlyFormStep::create()->setForm(\OnTheFlyForm\OnTheFlyForm::create([
            "shipping_mode",
        ], 'shipping-mode-key')
            ->setValidationRules([
                'shipping_mode' => ['required'],
            ])
        ));

        $builder->registerStep('payment', \StepFormBuilder\Step\OnTheFlyFormStep::create()->setForm(\OnTheFlyForm\OnTheFlyForm::create([
            "payment_mode",
        ], 'payment-mode-key')->setValidationRules([
            'payment_mode' => ['required'],
        ])));

        $builder->addGroup(['training1', 'training2', 'training3']);


        // mit-end:ThisApp
    }

    protected static function Ekom_configureCheckoutLayerProvider(\Module\Ekom\CheckoutLayerProvider\CheckoutLayerProvider $provider)
    {
        // mit-start:ThisApp
        $provider->setGetCheckoutLayerCallback(function () {
            $p = EkomSession::get("checkoutProvider", null);
            if ('ekomEstimate' === $p) {
                return \Module\EkomEstimate\Api\EkomEstimateApi::inst()->estimateCheckoutLayer();
            }
            return null;

        });
        // mit-end:ThisApp
    }

    protected static function Ekom_configureListBundle(\Module\Ekom\ListParams\ListBundleFactory\EkomListBundleFactory $factory)
    {
        // mit-start:ThisApp
        \Module\ThisApp\ListParams\ListBundleFactory\ThisAppListBundleFactoryHelper::registerListBundles($factory);
        // mit-end:ThisApp

        // mit-start:EkomEstimate
        \Module\EkomEstimate\ListParams\ListBundleFactory\EkomEstimateListBundleFactoryHelper::registerListBundles($factory);
        // mit-end:EkomEstimate

        // mit-start:EkomUserProductHistory
        \Module\EkomUserProductHistory\ListParams\ListBundleFactory\EkomUserProductHistoryBundleFactoryHelper::registerListBundles($factory);
        // mit-end:EkomUserProductHistory
    }

    protected static function Ekom_configureReferenceProvider(\Module\Ekom\Utils\ReferenceProvider $provider)
    {
        // mit-start:ThisApp
        $provider->setCallback('ekom', function () {

            EkomApi::inst()->initWebContext();
            $shopId = ApplicationRegistry::get("ekom.shop_id");
            $country = ApplicationRegistry::get("ekom.lang_iso");
            $date = date("Ymd_His");
            $userId = \Module\Ekom\Utils\E::getUserId("nc" . rand(1, 10000));
            $n = EkomApi::inst()->orderLayer()->countUserOrders($userId);
            $s = sprintf("%08s", $n + 1);
            return "LFE-$country-$date-$shopId-$userId-$s";
        });
        $provider->setCallback('ekomEstimate', function () {

            EkomApi::inst()->initWebContext();
            $shopId = ApplicationRegistry::get("ekom.shop_id");
            $country = ApplicationRegistry::get("ekom.lang_iso");
            $date = date("Ymd_His");
            $userId = \Module\Ekom\Utils\E::getUserId("nc" . rand(1, 10000));
            $n = EkomApi::inst()->orderLayer()->countUserOrders($userId);
            $s = sprintf("%08s", $n + 1);
            return "LFED-$country-$date-$shopId-$userId-$s";
        });
        // mit-end:ThisApp
    }

    protected static function Ekom_cart_decorateDefaultCart(array &$defaultCart)
    {

    }

    protected static function Ekom_CartLayer_decorate_mini_cart_model(array &$model)
    {
        // mit-start:ThisApp
        \Module\ThisApp\Ekom\Cart\CartModelDecorator::decorateModel($model);
        // mit-end:ThisApp
    }

    protected static function Ekom_createAccountAfter(array &$hookData)
    {
        // mit-start:ThisApp
        \Module\ThisApp\Api\ThisAppApi::inst()->userInfoLayer()->onCreateAccountAfter($hookData);
        // mit-end:ThisApp
    }


//    protected static function Ekom_decorateBoxModel(array &$model, array $contextualGet)
    protected static function Ekom_decorateBoxModel(array &$model)
    {
        // mit-start:EkomEvents
        \Module\EkomEvents\Helper\HooksHelper::decorateBoxModel($model);
        // mit-end:EkomEvents


        // mit-start:EkomTrainingProducts
        \Module\EkomTrainingProducts\Helper\HooksHelper::decorateBoxModel($model);
        // mit-end:EkomTrainingProducts

    }

//    protected static function Ekom_decorateBoxModelAfter(array &$model, array $contextualGet)
    protected static function Ekom_decorateBoxModelAfter(array &$model)
    {


    }

    protected static function Ekom_decorateBoxModelCacheable(array &$model)
    {
        // mit-start:EkomCardCombination
        \Module\EkomCardCombination\Api\EkomCardCombinationApi::inst()->productHelperLayer()->decorateBoxModel($model);
        // mit-end:EkomCardCombination

        // mit-start:EkomProductCardVideo
        \Module\EkomProductCardVideo\Api\EkomProductCardVideoApi::inst()->videoLayer()->decorateBoxModel($model);
        // mit-end:EkomProductCardVideo


        // mit-start:ThisApp
        \Module\ThisApp\Ekom\Helper\HooksHelper::decorateBoxModel($model);
        // mit-end:ThisApp


    }

    protected static function Ekom_decorateProductFeaturesBar_ViewConf(array &$conf, array $model)
    {
        // mit-start:EkomEvents
        \Module\EkomEvents\Helper\HooksHelper::Ekom_decorateProductFeaturesBar_ViewConf($conf, $model);
        // mit-end:EkomEvents
    }

    protected static function Ekom_decorateProductBoxClaws(\Kamille\Utils\Claws\Claws $claws, array $model)
    {
        // mit-start:EkomEvents
        \Module\EkomEvents\Helper\HooksHelper::Ekom_decorateProductBoxClaws($claws, $model);
        // mit-end:EkomEvents
    }

    protected static function Ekom_ProductHelper_removeProductById_after(array $idInfo)
    {

        // mit-start:EkomCardCombination
        \Module\EkomCardCombination\Api\EkomCardCombinationApi::inst()->productHelperLayer()->removeProductByIdCardIdInfo($idInfo);
        // mit-end:EkomCardCombination

    }

    protected static function Ekom_decorateProductIdToUniqueProductIdAdaptor(\Module\Ekom\Utils\ProductIdToUniqueProductIdAdaptor\ProductIdToUniqueProductIdAdaptor $adaptor)
    {

        // mit-start:EkomTrainingProducts
        $adaptor->addAdaptor('training_product', function ($productId, $meta = null) {
            return $productId . "-" . $meta;
        });
        // mit-end:EkomTrainingProducts
    }

    /**
     * @param array $configInfo , an array containing the following structure:
     *  - 0: viewId
     *  - 1: LawsConfig
     *
     */
//    protected static function Ekom_decorate_LawsConfig(array $configInfo)
//    {
//        list($viewId, $lawsConfig) = $configInfo;
//        /**
//         * @var $lawsConfig \Kamille\Utils\Laws\Config\LawsConfig
//         *
//         */
//        a($viewId);
//        $lawsConfig->replace(function (&$config) {
//            $productId = $config["widgets"]['maincontent.productBox']['conf']['product_id'];
//            $config["widgets"]['maincontent.productBox']["conf"]
//        });
//    }

    protected static function Ekom_feedAttributesModelGeneratorFactory(\Module\Ekom\ProductBox\AttributesModel\GeneratorFactory\EkomAttributesModelGeneratorFactory $factory)
    {
        // mit-start:EkomCardCombination
        $factory->setGenerator(function ($genInfo) {
            if ('child_of_ekom_card_combination' === $genInfo['extra']) {
                return new \Module\EkomCardCombination\ProductBox\AttributesModel\Generator\EkomCardCombinationAttributesModelGenerator();
            }

        });
        // mit-end:EkomCardCombination
    }

    protected static function Ekom_feedCarrierCollection(\Module\Ekom\Carrier\Collection\CarrierCollection $collection)
    {

        $collection->addCarrier("demo", \Ekom\Carrier\MockCarrier::create()->setReturnPrice(function ($orderInfo) {
            if ("28000" === $orderInfo['shippingAddress']['postcode']) {
                return 5;
            }
            return 3.90;// mit-start:EkomCardCombination
        })->setLabel("Demo")->setReturnDate(date("Y-m-d H:i:s", time() + 7 * 86400)));
        $collection->addCarrier('mock1', \Ekom\Carrier\MockCarrier::create()->setReturnPrice(100)->setLabel("Mock1")->setRejected([6, 7, 8]));
        $collection->addCarrier('mock2', \Ekom\Carrier\MockCarrier::create()->setReturnPrice(50)->setLabel("Mock2")->setRejected([6]));
        $collection->addCarrier('depot', \Ekom\Carrier\MockCarrier::create()->setReturnPrice(0)->setLabel("Dépôt"));
        $collection->addCarrier('carrier', \Ekom\Carrier\MockCarrier::create()->setReturnPrice(function ($d) {
            if ('37000' === $d['shippingAddress']['postcode']) {
                return 10.90;
            }
            return 3.90;
        })
            ->setLabel("Transporteur")->setReturnDate(function ($d) {
                if ('37000' === $d['shippingAddress']['postcode']) {
                    return date("Y-m-d H:i:s", time() + 15 * 86400);
                }
                return date("Y-m-d H:i:s", time() + 3 * 86400);
            }));
    }

    protected static function Ekom_feedCustomerMenu(\Models\AdminSidebarMenu\Lee\LeeAdminSidebarMenuModel $menu)
    {
        // mit-start:ThisApp
        /**
         * Right now we are in a hurry, so I will basically
         * do all my modules from the ThisApp's perspective, so I can gain some time,
         * I will do a default Ekom menu later..., which should end up here by the way
         */
        \Module\ThisApp\Ekom\Helper\HooksHelper::feedCustomerMenu($menu);
        // mit-end:ThisApp
    }

    protected static function Ekom_feedFrontControllerClaws(\Kamille\Utils\Claws\Claws $claws)
    {
        // mit-start:ThisApp
        \Module\ThisApp\Ekom\Helper\HooksHelper::feedFrontControllerClaws($claws);
        // mit-end:ThisApp


        //mit-start:EkomEstimate
        \Module\EkomEstimate\Api\EkomEstimateApi::inst()->frontControllerLayer()->feedFrontControllerClaws($claws);
        //mit-end:EkomEstimate
    }


    protected static function Ekom_feedOrderBuilderCollection(\Module\Ekom\Utils\OrderBuilder\Collection\OrderBuilderCollection $collection)
    {
        $collection->setBuilder("ekom", \Module\ThisApp\Ekom\Utils\OrderBuilder\ThisAppOrderBuilder::create());
        $collection->setBuilder("ekomEstimate", \Module\ThisApp\Ekom\Utils\OrderBuilder\ThisAppEstimateOrderBuilder::create());
    }


    protected static function Ekom_feedPaymentMethodHandlerCollection(\Module\Ekom\PaymentMethodHandler\Collection\PaymentMethodHandlerCollection $collection)
    {

        //mit-start:ThisApp
        $collection->setPaymentMethodHandler("credit_card_wallet", \Module\Ekom\PaymentMethodHandler\MockPaymentMethodHandler::create()
            ->setDefaultOptions(function () {
                return [
                    'cardMethod' => '1x',
                    'cardId' => \Module\PeiPei\Api\PeiPeiApi::inst()->creditCardWalletLayer()->getDefaultCardId(),
                ];
            })
            ->setConfig(function () {

                $userId = \Module\Ekom\Utils\E::getUserId();
                $cards = \Module\PeiPei\Api\PeiPeiApi::inst()->creditCardWalletLayer()->getUserCards($userId, true);
                return [
                    'cards' => $cards,
                    'has_3x' => true,
                    'has_4x' => true,
                    'default_card_type' => '1x',
                ];
            })
            ->setPayCallback(function (array $extendedOrderModel) {

                $res = \Module\PeiPei\Api\PeiPeiApi::inst()->ingenicoLayer()->payByAliasWithDirectLink($extendedOrderModel);
                if (true === $res->isSuccess()) {
//                    a($res->getStatus());
                    $values = $res->getValues();
                    if (array_key_exists('PAYID', $values)) {
                        $values['pay_id'] = $values['PAYID'];
                    }
                    return $values;
                } else {
                    $errCode = $res->getErrorCode();
                    $values = ArrayToStringTool::toPhpArray($res->getValues());
                    XLog::error("[ThisApp module] - Hooks Ekom_feedPaymentMethodHandlerCollection: payment failed with errorCode: $errCode and values $values");
                    throw new \Exception("Payment failed, please check the logs");
                }
            })
            /**
             * - type: the type of card being used
             * - last_four_digits:
             * - owner:
             * - expiration_date:
             * - recurrence: 1x, 3x, 4x
             */
            ->setPaymentDetailsCallback(function (array $userOptions) {
                $userId = \Module\Ekom\Utils\E::getUserId();
                $cardInfo = \Module\PeiPei\Api\PeiPeiApi::inst()->creditCardWalletLayer()->getUserCard($userId, $userOptions['cardId']);
                $ret = $cardInfo;
                $ret['recurrence'] = $userOptions['cardMethod'];
                return $ret;
            })
        );
        $collection->setPaymentMethodHandler("paypal", \Module\Ekom\PaymentMethodHandler\MockPaymentMethodHandler::create()
            ->setConfig(function () {
                return [];
            })
        );
        $collection->setPaymentMethodHandler("transfer", \Module\Ekom\PaymentMethodHandler\MockPaymentMethodHandler::create()
            ->setConfig(function () {
                return [];
            })
        );
        //mit-end:ThisApp
    }


//    protected static function Ekom_feedPaymentMethodHandlerCollection(\Module\Ekom\PaymentMethodHandler\Collection\PaymentMethodHandlerCollection $collection)
//    {
//        // pei pei module... manually
//        $collection->addPaymentMethodHandler("credit_card_wallet", \Module\PeiPei\PaymentMethodHandler\CreditCardWalletPaymentMethodHandler::create());
//        $collection->addPaymentMethodHandler("paypal", \Module\PeiPei\PaymentMethodHandler\PaypalPaymentMethodHandler::create());
//        // endof pei pei module... manually
//    }


    protected static function Ekom_feedPaymentMethodsContainer(\Kamille\Utils\Claws\Claws $claws)
    {
        // mit-start:PeiPei
        \Module\PeiPei\Helper\HooksHelper::feedPaymentMethodsContainer($claws);
        // mit-end:PeiPei
    }


    protected static function Ekom_feedCartAllowedExtraArgs(array $allowedExtraArgs)
    {
        // mit-start:EkomCardCombination
        $allowedExtraArgs[] = "cardCombinationItems";
        // mit-end:EkomCardCombination
    }


    protected static function Ekom_feedEkomProductPriceChain(\Module\Ekom\Price\PriceChain\EkomProductPriceChain $chain)
    {

    }

    protected static function Ekom_feedEkomCartPriceChain(\Module\Ekom\Price\PriceChain\EkomCartPriceChain $chain)
    {

    }

    protected static function Ekom_feedEkomTotalPriceChain(\Module\Ekom\Price\PriceChain\EkomTotalPriceChain $chain)
    {

    }

    protected static function Ekom_feedJsApiLoader(\Module\Ekom\JsApiLoader\EkomJsApiLoader $loader)
    {
        //mit-start:PeiPei
        $loader->addJsResource("/modules/PeiPei/js/peipeiJsApi.js");
        //mit-end:PeiPei

        //mit-start:EkomEstimate
        $loader->addJsResource("/modules/EkomEstimate/js/ekomEstimateJsApi.js");
        //mit-end:EkomEstimate
    }

    protected static function Ekom_feedEkomNotifier(\Module\Ekom\Notifier\EkomNotifier $notifier)
    {

    }

    protected static function Ekom_feedStatusProviderCollection(\Module\Ekom\Status\ProviderCollection\StatusProviderCollection $collection)
    {

    }

    protected static function Ekom_feedDynamicWidgetBinder(\Kamille\Utils\Laws\DynamicWidgetBinder\DynamicWidgetBinder $binder)
    {
        $binder->setListener("productListModifiers", \Module\Ekom\Laws\DynamicWidgetBinder\EkomProductListModifierListener::create());
    }


    protected static function Ekom_FrontController_decorateCommonWidgets($ball)
    {

        //mit-start:ThisApp
        \Module\ThisApp\Ekom\EkomConfigure::frontControllerDecorateCommonWidgets($ball);
        //mit-end:ThisApp

        //mit-start:EkomEstimate
        \Module\EkomEstimate\Api\EkomEstimateApi::inst()->frontControllerLayer()->decorateCommonWidgets($ball);
        //mit-end:EkomEstimate
    }


    protected static function Ekom_onPlaceOrderCleanedAfter()
    {
        //mit-start:ThisApp
        \Module\ThisApp\Ekom\Utils\CheckoutPage\CheckoutPageUtil::cleanSessionVars();
        //mit-end:ThisApp
    }

    protected static function Ekom_onPlaceOrderSuccessAfter($orderId, $orderInfo)
    {
        //mit-start:ThisApp
        \Module\ThisApp\Ekom\Helper\HooksHelper::Ekom_onPlaceOrderSuccessAfter($orderId, $orderInfo);
        //mit-end:ThisApp
    }


    protected static function Ekom_onProductVisited($productId, array $productDetails = [])
    {
        //mit-start:EkomUserProductHistoryApi
        \Module\EkomUserProductHistory\Api\EkomUserProductHistoryApi::inst()->generalLayer()->saveToHistory($productId, $productDetails);
        //mit-end:EkomUserProductHistoryApi
    }

    protected static function Ekom_onUserConnectedAfter()
    {
        //mit-start:EkomUserProductHistoryApi
        \Module\EkomUserProductHistory\Api\EkomUserProductHistoryApi::inst()->generalLayer()->transferSessionHistoryToDatabase();
        //mit-end:EkomUserProductHistoryApi
    }


    protected static function Ekom_prepareJsonService($service)
    {
//        if ('getProductInfo' === $service) {
//            if (array_key_exists("countryId", $_GET)) {
//                \Kamille\Architecture\Registry\ApplicationRegistry::set("Ekom.EkomTrainingProducts.countryId", $_GET['countryId']);
//            }
//            if (array_key_exists("city", $_GET)) {
//                \Kamille\Architecture\Registry\ApplicationRegistry::set("Ekom.EkomTrainingProducts.city", $_GET['city']);
//            }
//        }
    }

    protected static function Ekom_ProductBoxModel_collectExtraCaches(array $caches)
    {
        //mit-start:EkomEvents
        \Module\EkomEvents\Helper\HooksHelper::collectExtraCaches($caches);
        //mit-end:EkomEvents

        //mit-start:EkomTrainingProducts
        \Module\EkomTrainingProducts\Helper\HooksHelper::Ekom_ProductBoxModel_collectExtraCaches($caches);
        //mit-end:EkomTrainingProducts
    }


    protected static function Ekom_SearchResults_Provider(array &$model, array $context)
    {
        //mit-start:EkomFastSearch
        \Module\EkomFastSearch\Helper\HooksHelper::Ekom_SearchResults_Provider($model, $context);
        //mit-end:EkomFastSearch
    }


    protected static function Ekom_service_cartAddItem_decorateOutput(array &$out)
    {
        //mit-start:EkomEstimate
        \Module\EkomEstimate\Api\EkomEstimateApi::inst()->estimateCartLayer()->onCartAddItemAfterDecorateOutput($out);
        //mit-end:EkomEstimate
    }


    protected static function Ekom_updateItemQuantity_decorateCartModel(array &$cartModel, array $data)
    {
        //mit-start:EkomEvents
        \Module\EkomEvents\Helper\HooksHelper::Ekom_updateItemQuantity_decorateCartModel($cartModel, $data);
        //mit-end:EkomEvents
    }


    //--------------------------------------------
    // EKOM EVENTS
    //--------------------------------------------
    protected static function EkomEvents_availableCities(array &$cities)
    {
        //mit-start:ThisApp
        \Module\ThisApp\Ekom\Helper\HooksHelper::EkomEvents_availableCities($cities);
        //mit-end:ThisApp
    }


    //--------------------------------------------
    // EKOM TRAINING PRODUCTS
    //--------------------------------------------
    protected static function EkomTrainingProducts_availableCities(array &$cities)
    {
        //mit-start:ThisApp
        \Module\ThisApp\Ekom\Helper\HooksHelper::EkomTrainingProducts_availableCities($cities);
        //mit-end:ThisApp
    }

    //--------------------------------------------
    // CORE
    //--------------------------------------------
    protected static function Core_feedOnTheFlyFormProvider(\OnTheFlyForm\Provider\OnTheFlyFormProviderInterface $provider)
    {
        if ($provider instanceof \OnTheFlyForm\Provider\OnTheFlyFormProvider) {
            $provider->setNamespace("Ekom", 'Module\Ekom\OnTheFlyForm');
            $provider->setNamespace("PeiPei", 'Module\PeiPei\OnTheFlyForm');
            $provider->setNamespace("ThisApp", 'Module\ThisApp\OnTheFlyForm');
        }
    }

    protected static function Core_configureRoutsyRouter(\Kamille\Utils\Routsy\RoutsyRouter $router)
    {
        $router->addCollection(\Kamille\Utils\Routsy\RouteCollection\PrefixedRoutsyRouteCollection::create()
            ->setFileName("ultimo")
            ->setOnRouteMatch(function () {
                \Kamille\Architecture\ApplicationParameters\ApplicationParameters::set("theme", "ultimo");
            })
            ->setUrlPrefix(\Kamille\Services\XConfig::get("/ultimo"))
        );
    }


    protected static function Core_onSiteConfigured(\Kamille\Architecture\Request\Web\HttpRequestInterface $request)
    {
        $uri = $request->uri();
        if ('/ultimo' === substr($uri, -7)) {
            \Kamille\Architecture\ApplicationParameters\ApplicationParameters::set("theme", "ultimo");


//            \Kamille\Architecture\ApplicationParameters\ApplicationParameters::set("theme", "ultimo");
            if ($request instanceof \Kamille\Architecture\Request\Web\HttpRequest) {
                $request->hack([
                    'REQUEST_URI' => substr($request->uri(), 0, -7)
                ]);
            }
        }
    }

    protected static function Core_configureLawsUtil(\Kamille\Utils\Laws\LawsUtil $util)
    {
        $util->addShortCodeProvider(\Module\Ekom\ShortCodeProvider\EkomShortCodeProvider::create());
    }


    protected static function Core_addLoggerListener(\Logger\LoggerInterface $logger)
    {
        if (true === \Kamille\Services\XConfig::get("Core.useFileLoggerListener")) {
            $f = \Kamille\Services\XConfig::get("Core.logFile");
            $logger->addListener(\Logger\Listener\FileLoggerListener::create()
                ->setFormatter(\Logger\Formatter\TagFormatter::create())
//                ->setIdentifiers(['tmp'])
                ->setIdentifiers(null)
                ->removeIdentifier("sql.log")
                ->removeIdentifier("tabatha")
                ->removeIdentifier("hooks")
                ->setPath($f));
        }


        if (true === \Kamille\Services\XConfig::get("Core.useDbLoggerListener")) {

            $f = \Kamille\Services\XConfig::get("Core.dbLogFile");
            $logger->addListener(\Logger\Listener\FileLoggerListener::create()
                ->addIdentifier('sql.log')
                ->setFormatter(\Logger\Formatter\TagFormatter::create())
                ->setPath($f));
        }
    }

    protected static function Core_feedEarlyRouter(\Module\Core\Architecture\Router\EarlyRouter $router)
    {
        // mit-start:Authenticate
        $router->addRouter(\Module\Authenticate\Architecture\Router\AuthenticateRouter::create());
        // mit-end:Authenticate
    }


    protected static function Core_autoLawsConfig(&$data)
    {


        // mit-start:NullosAdmin
        $autoJsScript = "/theme/" . \Kamille\Architecture\ApplicationParameters\ApplicationParameters::get("theme") . "/controllers/" . \Bat\ClassTool::getShortName($data[0]) . ".js";
        $file = \Kamille\Architecture\ApplicationParameters\ApplicationParameters::get("app_dir") . "/www" . $autoJsScript;
        if (file_exists($file)) {
            /**
             * @var $conf \Kamille\Utils\Laws\Config\LawsConfig
             */
            $conf = $data[1];
            $conf->replace(function (array &$c) use ($autoJsScript) {
                $c['layout']['conf']["jsScripts"][] = $autoJsScript;
            });
        }
        // mit-end:NullosAdmin
    }

    protected static function Core_feedAjaxUri2Controllers(array &$uri2Controllers)
    {
    }

    protected static function Core_addLawsUtilProxyDecorators(\Kamille\Mvc\LayoutProxy\LawsLayoutProxyInterface $layoutProxy)
    {
        if ($layoutProxy instanceof \Kamille\Mvc\LayoutProxy\LawsLayoutProxy) {
            $layoutProxy->addDecorator(\Kamille\Mvc\WidgetDecorator\PositionWidgetDecorator::create());
        }

        // mit-start:NullosAdmin
        if ($layoutProxy instanceof \Kamille\Mvc\LayoutProxy\LawsLayoutProxy) {
            $layoutProxy->addDecorator(\Kamille\Mvc\WidgetDecorator\Bootstrap3GridWidgetDecorator::create());
        }
        // mit-end:NullosAdmin
    }

    protected static function Core_lazyJsInit_addCodeWrapper(\Module\Core\JsLazyCodeCollector\JsLazyCodeCollectorInterface $collector)
    {
        if (true === \Kamille\Services\XConfig::get('Core.addJqueryEndWrapper')) {
            $collector->addCodeWrapper('jquery', function ($s) {
                $r = '$(document).ready(function () {' . PHP_EOL;
                $r .= $s;
                $r .= '});' . PHP_EOL;
                return $r;
            });
        }
    }


    protected static function Core_widgetInstanceDecorator(\Kamille\Mvc\Widget\WidgetInterface $widget)
    {
        $widget->setTemplate("Core/widget-error");
    }

    protected static function Core_ModalGscpResponseDefaultButtons(array &$buttons)
    {


        // mit-start:NullosAdmin
        $buttons = [
            "close" => [
                "flavour" => "default",
                "label" => "Close",
                "htmlAttr" => [
                    "data-dismiss" => "modal",
                ],
            ],
        ];
        // mit-end:NullosAdmin
    }

    protected static function Core_feedListModifierCircle(\ListModifier\Circle\ListModifierCircle $circle)
    {
    }


    //--------------------------------------------
    // DATATABLE
    //--------------------------------------------
    protected static function DataTable_getRendererClassName(&$renderer)
    {


        // mit-start:NullosAdmin
        $renderer = 'Module\NullosAdmin\ModelRenderers\DataTable\NullosDataTableRenderer';
        // mit-end:NullosAdmin
    }


    protected static function DataTable_configureProfileFinder(\Module\DataTable\DataTableProfileFinder\DataTableProfileFinder $profileFinder)
    {
        // mit-start:AutoAdmin
        $profileFinder->addFallbackHandler(function ($dir, $profileId) {
            $p = explode('/', $profileId);
            if (3 === count($p)) {
                $manual = implode('/', [$p[0], 'manual', $p[1], $p[2]]);
                $f = $dir . "/$manual.php";
                if (file_exists($f)) {
                    return $f;
                } else {
                    $auto = implode('/', [$p[0], 'auto', $p[1], $p[2]]);
                    $f = $dir . "/$auto.php";
                    if (file_exists($f)) {
                        return $f;
                    }
                }
            }
            return false;
        });
        // mit-end:AutoAdmin
    }


    //--------------------------------------------
    // NULLOS ADMIN
    //--------------------------------------------
    protected static function NullosAdmin_layout_addTopBarRightWidgets(array &$topbarRightWidgets)
    {
        // mit-start:Ekom
        $prefixUri = "/theme/" . \Kamille\Architecture\ApplicationParameters\ApplicationParameters::get("theme");
        $imgPrefix = $prefixUri . "/production";

        unset($topbarRightWidgets['topbar_right.userMessages']);

        $topbarRightWidgets["topbar_right.shopListDropDown"] = [
            "tpl" => "Ekom/ShopListDropDown/prototype",
            "conf" => [
                'nbMessages' => 10,
                'badgeColor' => 'red',
                'showAllMessagesLink' => true,
                'allMessagesText' => "See All Alerts",
                'allMessagesLink' => "/user-alerts",
                "messages" => [
                    [
                        "link" => "/ji",
                        "title" => "John Smith",
                        "image" => $imgPrefix . '/images/ling.jpg',
                        "aux" => "3 mins ago",
                        "message" => "Film festivals used to be do-or-die moments for movie makers. They were where...",
                    ],
                    [
                        "link" => "/ji",
                        "title" => "John Smith",
                        "image" => $imgPrefix . '/images/img.jpg',
                        "aux" => "12 mins ago",
                        "message" => "Film festivals used to be do-or-die moments for movie makers. They were where...",
                    ],
                ],
            ],
        ];
        // mit-end:Ekom
    }

    protected static function NullosAdmin_layout_sideBarMenuModel(array &$sideBarMenuModel)
    {
        // mit-start:AutoAdmin
        $allItems = [];
        $dir = \Module\AutoAdmin\AutoAdminHelper::getGeneratedSideBarMenuPath();
        $autoDir = $dir . "/auto";
        $manualDir = $dir . "/manual";
        if (is_dir($autoDir)) {
            $dbFiles = \DirScanner\YorgDirScannerTool::getFilesWithExtension($autoDir, 'php', false, false, true);
            foreach ($dbFiles as $dbFile) {
                $items = [];
                $manualFile = $manualDir . "/$dbFile";
                if (file_exists($manualFile)) {
                    include $manualFile;
                } else {
                    include $autoDir . "/$dbFile";
                }
                $allItems[] = $items;
            }

            $sideBarMenuModel['sections'][] = [
                "label" => "AutoAdmin",
                "items" => $allItems,
            ];
        }
        // mit-end:AutoAdmin

        // mit-start:Ekom
        $sideBarMenuModel['sections'][] = [
            "label" => "Ekom",
            "items" => [
                [
                    "icon" => "fa fa-home",
                    "label" => "test",
                    'badge' => [
                        'type' => "success",
                        'text' => "success",
                    ],
                    "items" => [
                        [
                            "icon" => "fa fa-but",
                            "label" => "bug",
                            "link" => "/pou",
                            "items" => null,
                        ],
                    ],
                ],
            ],
        ];
        // mit-end:Ekom
    }


}

