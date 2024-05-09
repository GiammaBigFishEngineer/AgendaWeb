<?php
require_once(__ROOT__ . '/views/BaseView.php');

require_once(__ROOT__ . '/config/EnvLoader.php');

require_once(__ROOT__ . '/vendor/autoload.php');

class MailView extends BaseView
{
    public function renderAuthorizeRequest($id_user, $email, $requested_at, $token)
    {
        return $this->twig->render('mail/authorize.request.html.twig', [
            'id_user' => $id_user,
            'email' => $email,
            'requested_at' => $requested_at,
            'authorize_link' => __BASEURL__ . '/authorize?token=' . $token
        ]);
    }

    public function renderForgotten($token)
    {
        return $this->twig->render('mail/forgotten.html.twig', [
            'reset_link' => __BASEURL__ . '/reset_password?token=' . $token
        ]);
    }
}