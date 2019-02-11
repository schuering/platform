<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Store\Api;

use Shopware\Core\Framework\Store\Services\StoreClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends AbstractController
{
    /**
     * @var StoreClient
     */
    private $storeClient;

    public function __construct(StoreClient $storeClient)
    {
        $this->storeClient = $storeClient;
    }

    /**
     * @Route("/api/v{version}/_custom/store/login", name="api.custom.store.login", methods={"POST"})
     */
    public function login(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $accessTokenStruct = $this->storeClient->loginWithShopwareId($data['shopwareId'], $data['password']);

        $response = new JsonResponse($accessTokenStruct->toArray());
        $response->headers->setCookie(new Cookie('store_token', $accessTokenStruct->getToken()));

        return $response;
    }

    /**
     * @Route("/api/v{version}/_custom/store/checklogin", name="api.custom.store.checklogin", methods={"GET"})
     */
    public function checkLogin(Request $request): Response
    {
        $token = $request->cookies->get('store_token');

        $isLoggedIn = $this->storeClient->checkLogin($token);

        $response = new JsonResponse([
            'success' => $isLoggedIn,
        ]);

        return $response;
    }

    /**
     * @Route("/api/v{version}/_custom/store/licenses", name="api.custom.store.licenses", methods={"GET"})
     */
    public function getLicenseList(Request $request): Response
    {
        $token = $request->cookies->get('store_token', '');

        $licenseList = $this->storeClient->getLicenseList($token);

        return new JsonResponse([
            'items' => $licenseList,
            'total' => count($licenseList),
        ]);
    }
}
