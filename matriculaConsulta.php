	<?php
/**
 *	Mödulo para agrupar los pedidos en una entidad superior : matrícula. Junto
 *	con la matrícula, se ha creado también la entidad Semestre.
 *	No preisa controlador (está enganchado a un hook que ejectua un componente
 * 	lógico cuando el hook se ejecuta  -al pulsar el botón de creación de pedido-)
 */

if (!defined('_PS_VERSION_'))
	exit;

// configuración del módulo en back office, con pestaña nav lateral
class MatriculaConsulta extends Module {

	public function __construct() {
	$this->name = 'matriculaConsulta';
	$this->tab = 'front_office_features';
	$this->version = '1.0.0';
	$this->author = 'IT';
	$this->need_instance = 0;
	$this->ps_versions_compliancy = array('min'=>'1.6','max'=>_PS_VERSION_);
	$this->bootstrap = true;
	parent::__construct();
	$this->displayName = $this->l('Consulta matriculas');
	$this->description = $this->l('Modulo Consulta Matricula');
	$this->confirmUninstall = $this->l('¿Desea desinstalar?');
	}

	public function install() {
		// 	este módulo está registrado al hook 'actionValidateOrder'
		if (!parent::install() || !$this->registerHook('actionValidateOrder') ||!$this->registerHook('ActionAdminControllerSetMedia'))
			return false;
		return true;
	}

	public function uninstall() {
		if (!parent::uninstall() || !$this->unregisterHook('ActionAdminControllerSetMedia')||!$this->unregisterHook('actionValidateOrder'))
			return false;
		return true;
	}


	/**
	*		el módulo está registrado a hook que, entre otras cosas, 'recoge'
	*		los javascript que se creen. En este caso será matriculaConsulta.js
	*/

	/**
	 *	se crea un método estático que se usará en el metodo del hook. Retorna
	 *	el id de la matricula activa del alumno
	 *	$idSemestre: id del semestre actual
	 *	$idCustomer: id del alumno
	 */
	public static function getActiveMatriculaCustomer($idSemestre, $idCustomer){
		$result = Db::getInstance()->executeS('select id_matricula from ps_matricula where id_customer='.$idCustomer.' and id_semestre='.$idSemestre);
		return $resultado=$result[0]['id_matricula'];
  }

/**
 * método llamará el hook al que el módulo esta registrado
 */
	public function hookActionValidateOrder($params) {
			$idSemestreActivo=Semestre::getIdActive();
			$semestreActivo= new Semestre($idSemestreActivo);
			if(!$semestreActivo){
				$this->errors[] = $this->trans('no hay semestres activo', array(), 'Admin.Catalog.Notification');
				return;
			}

			$id_customer=$params['customer']->id;
			$id_matricula=self::getActiveMatriculaCustomer($idSemestreActivo,$id_customer);
			if(!$id_matricula){
					$matricula = new MatriculaObject();
					$matricula->id_customer=$id_customer;
					$matricula->id_semestre=$idSemestreActivo;
					$matricula->active=1;
					$matricula->add();
					$id_matricula=$matricula->id;
			}
			$matriculaOrder = new MatriculaOrder();
					$matriculaOrder->id_matricula=$id_matricula;
					$matriculaOrder->id_order=$params['order']->id;
					$matriculaOrder->active=1;
					$matriculaOrder->add();



	}



	}
