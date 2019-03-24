<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Greeters\Service\User;

use XF\Entity\User;
use XF\Service\AbstractService;

/**
 * A service for notifying users about users.
 */
class Notifier extends AbstractService
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $notifyJoined = [];

    /**
     * @var array
     */
    protected $usersAlerted = [];

    /**
     * @param \XF\App $app
     * @param User    $user
     */
    public function __construct(\XF\App $app, User $user)
    {
        parent::__construct($app);

        $this->user = $user;
    }

    /**
     * @param array $users
     */
    public function setNotifyJoined(array $users)
    {
        $this->notifyJoined = array_unique($users);
    }

    /**
     * @return array
     */
    public function getNotifyJoined()
    {
        return $this->notifyJoined;
    }

    /**
     * Sends notifications to users.
     */
    public function notify()
    {
        $notifiableUsers = $this->getUsersForNotification();

        foreach ($this->notifyJoined as $userId) {
            if (!isset($notifiableUsers[$userId])) {
                continue;
            }

            $this->sendNotification($notifiableUsers[$userId], 'join');
        }
        $this->notifyJoined = [];

        $this->usersAlerted = [];
    }

    /**
     * @return array
     */
    protected function getUsersForNotification()
    {
        $userIds = array_unique(array_merge($this->getNotifyJoined()));

        /** @var \XF\Mvc\Entity\AbstractCollection $users */
        $users = $this->em()->findByIds('XF:User', $userIds, [
            'Profile',
            'Option'
        ]);
        if (!$users->count()) {
            return [];
        }

        $users = $users->filter(function (User $user) {
            return \XF::asVisitor($user, function () {
                return $this->user->canPostOnProfile();
            });
        });

        return $users->toArray();
    }

    /**
     * @param User   $user
     * @param string $action
     *
     * @return bool
     */
    protected function sendNotification(User $user, $action)
    {
        if ($user->user_id == $this->user->user_id) {
            return false;
        }

        if (!empty($this->usersAlerted[$user->user_id])) {
            return false;
        }

        /** @var \XF\Repository\UserAlert $alertRepo */
        $alertRepo = $this->repository('XF:UserAlert');
        $alert = $alertRepo->alert(
            $user,
            $this->user->user_id,
            $this->user->username,
            'user',
            $this->user->user_id,
            $action
        );
        if (!$alert) {
            return false;
        }

        $this->usersAlerted[$user->user_id] = true;
        return true;
    }
}
