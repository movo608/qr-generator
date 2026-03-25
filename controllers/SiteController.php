<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\ContactForm;
use app\modules\qrcode\models\QrCode;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->render('index');
        }

        $userId = Yii::$app->user->id;
        $query = QrCode::find()->where(['user_id' => $userId]);

        // KPI stats
        $totalQrCodes = (clone $query)->count();
        $uniqueArtists = (clone $query)->select('creator')->distinct()->count('creator');
        $uniqueAlbums = (clone $query)->select('product')->distinct()->count('product');
        $monthStart = strtotime(date('Y-m-01'));
        $thisMonth = (clone $query)->andWhere(['>=', 'created_at', $monthStart])->count();

        // QR codes created per day (last 30 days)
        $dailyData = (clone $query)
            ->select([
                'DATE(FROM_UNIXTIME(created_at)) as date',
                'COUNT(*) as count',
            ])
            ->andWhere(['>=', 'created_at', strtotime('-30 days')])
            ->groupBy('date')
            ->orderBy('date')
            ->asArray()
            ->all();

        // Top 10 artists by QR code count
        $topArtists = (clone $query)
            ->select(['creator', 'COUNT(*) as count'])
            ->groupBy('creator')
            ->orderBy(['count' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        // Top 10 albums by QR code count
        $topAlbums = (clone $query)
            ->select(['product', 'COUNT(*) as count'])
            ->groupBy('product')
            ->orderBy(['count' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        // Latest 5 QR codes
        $latestCodes = (clone $query)
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'totalQrCodes' => $totalQrCodes,
            'uniqueArtists' => $uniqueArtists,
            'uniqueAlbums' => $uniqueAlbums,
            'thisMonth' => $thisMonth,
            'dailyData' => $dailyData,
            'topArtists' => $topArtists,
            'topAlbums' => $topAlbums,
            'latestCodes' => $latestCodes,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. You can now log in.');
            return $this->redirect(['site/login']);
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }
}
