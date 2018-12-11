<?phpnamespace app\modules\api\admin\controllers;use app\lib\bl\Enum;use app\models\admin\SysPropsModel;use Yii;use yii\web\BadRequestHttpException;class PropsController extends BaseController{    protected static $apiModelName = 'app\models\admin\SysPropsModel';    public function actionSet()    {        $params = Yii::$app->request->bodyParams;        if (empty($params['type']) || empty($params['id']))        {            throw new BadRequestHttpException('params invalid');        }        if (empty($params['name']))        {            $params['name'] = Enum::getValue(Enum::$ADMIN_PM_MAP, $params['type'], $params['id']);        }        return SysPropsModel::setProp($params);    }    public function actionGet()    {        $params = Yii::$app->request->queryParams;        if (empty($params['type']) || empty($params['id']))        {            throw new BadRequestHttpException('params invalid');        }        return SysPropsModel::getProp($params['type'], $params['id']);    }}