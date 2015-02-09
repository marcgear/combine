<?php
namespace Combine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$app = require_once __DIR__.'/../init.php';

$app->get('/login', function (Request $request) use ($app) {
    $error = $app['security.last_error']($request);
    if ($error) {
        $app['combine.msg']['error'] = $error. '. I don\'t know you.';
    }

    return $app['twig']->render('login.twig', array(
        'msg'          => $app['combine.msg'],
        'lastUsername' => $app['session']->get('_security.last_username'),
        'nextCutoff'   => $app['combine.next_cutoff'],
    ));
});

$app->get('/', function () use ($app) {

    $user = $app['security']->getToken()->getUser();
    if (!($user instanceof User)) {
        // need to promote the user to a full User by persisting it to the db
        $user = $app['combine.gateway.user']->saveUser($user);
        $app['security']->getToken()->setUser($user);
    }

    if (!($user->getDepartment() && $user->getName())) {
        $app['combine.msg']['info'] = 'Your profile isn\'t complete - fill in your settings';
    }


    $hasSubmitted = $app['combine.gateway.entry']->userHasSubmitted(
        $app['security']->getToken()->getUser()->getUsername(),
        $app['combine.last_cutoff']
    );

    return $app['twig']->render('index.twig', array(
        'user'         => $app['security']->getToken()->getUser(),
        'msg'          => $app['combine.msg.collect'],
        'nextCutoff'   => $app['combine.next_cutoff'],
        'hasSubmitted' => $hasSubmitted,
    ));
});

$app->get('/settings', function (Request $request) use ($app) {
    return $app['twig']->render('settings.twig', array(
        'user'=> $app['security']->getToken()->getUser(),
        'msg'  => $app['combine.msg.collect'],
    ));
});

$app->post('/settings', function (Request $request) use ($app) {
    $user = $app['security']->getToken()->getUser();
    $user->setName($request->request->get('name'));
    $user->setDepartment($request->request->get('department'));
    $app['combine.gateway.user']->saveUser($user);

    // redirect to / with thanks banner
    $app['session']->set('combine.message.success', 'You saved your settings');
    return $app->redirect('/');
});


$app->get('/file', function (Request $request) use ($app) {
    $questions = $app['combine.gateway.entry']->loadEntriesForUser(
        $app['security']->getToken()->getUser()->getUsername(),
        $app['combine.last_cutoff']
    );

    $errors  = $request->query->get('errors');
    $answers = $request->query->get('answers');
    foreach ($questions as $key => $question) {
        $questionId = $question['id'];

        if (isset($answers[$questionId])) {
            $questions[$key]['answer'] = $answers[$questionId];
        }

        if (isset($errors[$questionId])) {
            $questions[$key]['hasError'] = true;
        }
    }

    return $app['twig']->render('form.twig', array(
        'user'      => $app['security']->getToken()->getUser(),
        'questions' => $questions,
        'msg'       => $app['combine.msg'],
    ));
});

$app->post('/file', function (Request $request) use ($app) {

    // Has the user already submitted - is this an update?
    $hasSubmitted = $app['combine.gateway.entry']->userHasSubmitted(
        $app['security']->getToken()->getUser()->getUsername(),
        $app['combine.last_cutoff']
    );

    $errors = array();

    // persist the report
    $answers = $request->request->get('answer');
    $entries = $request->request->get('entry');

    foreach ($answers as $questionId => $answer) {

        if (strlen($answer)) {
             $app['combine.gateway.entry']->saveEntry(
                $answer,
                (int) $questionId,
                $app['security']->getToken()->getUser()->getUsername(),
                $entries[$questionId]
            );
        } else {
            $errors[$questionId] = 'Missing answer';
        }
    }

    // any errors - show the form again
    if ($errors) {
        $app['combine.msg']['error'] = 'You gotta fill in all your report.';
        //$params = $request->request->getIterator()->getArrayCopy();
        $subRequest = Request::create('/file', 'GET', array(
            'errors'  => $errors,
            'answers' => $answers,
        ));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    // redirect to / with thanks banner
    if ($hasSubmitted) {
        $msg = 'You saved your changes';
    } else {
        $msg = 'You filed your report on time';
    }

    $app['session']->set('combine.message.success', $msg);
    return $app->redirect('/');
});

$app->get('/reports/latest', function () use ($app) {
    // redirect to latest report
    $cutoff = $app['combine.next_cutoff'];
    $year   = date('Y', $cutoff);
    $month  = date('m', $cutoff);
    $day    = date('d', $cutoff);
    $date   = sprintf('%d/%d/%d', $year, $month, $day);
    return $app->redirect('/reports/'.$date);
});

$app->get('/reports', function () use ($app) {
    return $app['twig']->render('reports.twig');
});

$app->get('/reports/{year}/{month}/{day}', function ($year, $month, $day) use ($app) {
    $end = mktime(0,0,0, $month, $day, $year);
    $start = $end - WEEK;

    $questions = $app['combine.gateway.entry']->loadEntriesForPeriod($start, $end);
    return $app['twig']->render('report.twig', array(
        'user'      => $app['security']->getToken()->getUser(),
        'date'      => $end,
        'questions' => $questions,
    ));
});

$app->run();