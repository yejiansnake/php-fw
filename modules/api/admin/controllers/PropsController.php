<?phpnamespace app\modules\api\admin\controllers;use app\lib\bl\Enum;use app\models\admin\SysPropsModel;use Yii;use yii\web\BadRequestHttpException;class PropsController extends BaseController{    protected static $apiModelName = 'app\models\admin\SysPropsModel';    protected static $apiModelOptions = [        self::ACTION_INDEX,        self::ACTION_VIEW,        self::ACTION_CREATE,        self::ACTION_UPDATE,        self::ACTION_DELETE,        self::ACTION_SIMPLE,    ];    protected static $isPagination = false;    public function actionSimple()    {        $params = Yii::$app->request->queryParams;        if (empty($params['type']))        {            throw new BadRequestHttpException('params invalid');        }        $props = $this->actionSimpleImp($params, ['tid', 'name']);        $items = [];        foreach ($props['items'] as $prop)        {            $items[] = ['id' => $prop['tid'], 'name' => $prop['name']];        }        return ['items' => $items];    }    public function actionSet()    {        $params = Yii::$app->request->bodyParams;        if (empty($params['type']) || empty($params['id']))        {            throw new BadRequestHttpException('params invalid');        }        if (empty($params['name']))        {            $params['name'] = Enum::getValue(Enum::$ADMIN_PM_MAP, $params['type'], $params['id']);        }        return SysPropsModel::setProp($params);    }    public function actionGet()    {        $params = Yii::$app->request->queryParams;        if (empty($params['type']) || empty($params['id']))        {            throw new BadRequestHttpException('params invalid');        }        return SysPropsModel::getProp($params['type'], $params['id']);    }}