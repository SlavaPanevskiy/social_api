<?php

namespace App\Models\Repositories;

use App\Services\Neo4j;

/**
 *  Node Repository
 */
class Node
{
    const QUERY_TEMPLATE_FRIEND_REQUESTS = "START current=node({id}) MATCH user-[:PENDING]->current RETURN user";
    const QUERY_TEMPLATE_GET_USER_FRIENDS = "START current=node({userId}) MATCH current-[:FRIENDS]->user RETURN user";
    const QUERY_TEMPLATE_GET_ALL_USERS = "MATCH (user) RETURN user";
    const QUERY_TEMPLATE_GET_USERS_BY_CIRCLE = "START current=node({id}) MATCH ps = (current-[:FRIENDS*{depth}]->user) MATCH p = allShortestPaths(current-[r:FRIENDS*..{depth}]->user) WHERE length(p) = {depth} RETURN user";

    /**
     * @var Neo4j
     */
    private $_neo4j;

    /**
     * @param Neo4j $neo4j
     */
    public function __construct(Neo4j $neo4j = null)
    {
        $this->_neo4j = is_null($neo4j) ? new Neo4j() : $neo4j;
    }

    /**
     * Get node by id.
     *
     * @param $id
     * @return \Everyman\Neo4j\Node
     * @throws \Everyman\Neo4j\Exception
     */
    public function getNodeById($id)
    {
        return $this->_neo4j->getClient()->getNode($id);
    }

    /**
     * Get list with request ti friend.
     *
     * @param string $id
     * @return array
     */
    public function getFriendRequestUserList($id)
    {
        $queryTemplate = strtr(
             self::QUERY_TEMPLATE_FRIEND_REQUESTS,
             array(
                '{id}' => $id
             )
         );
        return $this->_userListQuery($queryTemplate);
    }

    /**
     * Get user friend list
     *
     * @param $userId
     * @return array
     */
    public function getUserFriendList($userId)
    {
        $queryTemplate = strtr(
            self::QUERY_TEMPLATE_GET_USER_FRIENDS,
            array(
                '{userId}' => $userId
            )
        );
        return $this->_userListQuery($queryTemplate);
    }

    /**
     * Get user list from circle with selected depth.
     *
     * @param int $id    User id.
     * @param int $depth Circle depth.
     * @return array
     */
    public function getUserListFromCircle($id, $depth)
    {
        $queryTemplate = strtr(
            self::QUERY_TEMPLATE_GET_USERS_BY_CIRCLE,
            array(
                '{id}' => $id,
                '{depth}' => $depth,
            )
        );
        return $this->_userListQuery($queryTemplate);
    }

    /**
     * Get list of all users.
     *
     * @return array
     */
    public function getAllUsersList()
    {
        return $this->_userListQuery(self::QUERY_TEMPLATE_GET_ALL_USERS);
    }

    /**
     * Return user list [id -> name] from request.
     *
     * @param string $queryTemplate Query template for neo4j request.
     * @return array
     */
    private function _userListQuery($queryTemplate)
    {
        $resultSet = $this->_neo4j->query($queryTemplate);
        $result = array();
        foreach ($resultSet as $node) {
            $userId = $node['user']->getId();
            $userName = $node['user']->getProperty('name');
            $result[$userId] = $userName;
        }
        return $result;
    }
}