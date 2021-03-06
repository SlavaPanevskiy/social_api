<?php

namespace App\Http\Controllers;

use App\Models\Repositories;
use App\Models\Services;
use App\Services\Neo4j;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Class RelationController
 * @package App\Http\Controllers
 */
class RelationController extends BaseController
{
    /**
     * @var Repositories\Node
     */
    private $_nodeRepository;

    /**
     * @var Services\Relations
     */
    private $_relationService;


    /**
     * @param Repositories\Node $nodeRepository
     * @param Repositories\Relations $relationRepository
     * @param Services\Relations $relationService
     */
    public function __construct(
        Repositories\Node $nodeRepository,
        Repositories\Relations $relationRepository,
        Services\Relations $relationService
    )
    {
        $this->_nodeRepository = $nodeRepository;
        $this->_relationRepository = $relationRepository;
        $this->_relationService = $relationService;
    }

    /**
     * Action for sending friend request.
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function sendFriendRequest(Request $request, $id)
    {
        $fromUser = $this->_nodeRepository->getNodeById($id);
        $toUser = $this->_nodeRepository->getNodeById($request->get('userid'));
        $relationship = $fromUser->relateTo($toUser, 'PENDING')->save();
        return new Response(['id' => $relationship->getId()], Response::HTTP_OK);
    }

    /**
     * Reject friend request from user with userid to user with id.
     *
     * @param string $id
     * @param string $userId
     * @return Response
     */
    public function rejectFriendRequest($id, $userId)
    {
        $relationship = $this->_relationRepository->getPendingRelation($id, $userId);
        $this->_relationService->rejectFriendRequest($relationship);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Get list user with friend request.
     *
     * @param $id
     * @return Response
     */
    public function getFriendRequestUsers($id)
    {
        return new Response($this->_nodeRepository->getFriendRequestUserList($id), Response::HTTP_OK);
    }

    /**
     * Approve friend request.
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function approveFriendRequest(Request $request, $id)
    {
        $userId = $request->get('userid');
        $relationship = $this->_relationRepository->getPendingRelation($id, $userId);
        $this->_relationService->approveFriendRequest($relationship);
        return new Response('', Response::HTTP_OK);
    }
}
