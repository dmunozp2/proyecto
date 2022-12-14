<?php 

class ajaxController extends Controller {


  /**
   * La petición del servidor
   *
   * @var string
   */
  private $r_type = null;

  /**
   * Hook solicitado para la petición
   *
   * @var string
   */
  private $hook   = null;

  /**
   * Tipo de acción a realizar en ajax
   *
   * @var string
   */
  private $action = null;

  /**
   * Token csrf de la sesión del usuario que solicita la petición
   *
   * @var string
   */
  private $csrf   = null;

  /**
   * Todos los parámetros recibidos de la petición
   *
   * @var array
   */
  private $data   = null;

  /**
   * Parámetros parseados en caso de ser petición put | delete | headers | options
   *
   * @var mixed
   */
  private $parsed = null;

  /**
   * Valor que se deberá proporcionar como hook para
   * aceptar una petición entrante
   *
   * @var string
   */
  private $hook_name        = 'bee_hook'; // Si es modificado, actualizar el valor en la función core insert_inputs()
  
  /**
   * parámetros que serán requeridos en TODAS las peticiones pasadas a ajaxController
   * si uno de estos no es proporcionado la petición fallará
   *
   * @var array
   */
  private $required_params  = ['hook', 'action'];

  /**
   * Posibles verbos o acciones a pasar para nuestra petición
   *
   * @var array
   */
  private $accepted_actions = ['get', 'post', 'put', 'delete', 'options', 'headers', 'add', 'load'];

  function __construct()
  {
    // Parsing del cuerpo de la petición
    $this->r_type = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
    $this->data   = in_array($this->r_type, ['PUT','DELETE','HEADERS','OPTIONS']) ? parse_str(file_get_contents("php://input"), $this->parsed) : ($this->r_type === 'GET' ? $_GET : $_POST);
    $this->data   = $this->parsed !== null ? $this->parsed : $this->data;
    $this->hook   = isset($this->data['hook']) ? $this->data['hook'] : null;
    $this->action = isset($this->data['action']) ? $this->data['action'] : null;
    $this->csrf   = isset($this->data['csrf']) ? $this->data['csrf'] : null;

    // Validar que hook exista y sea válido
    if ($this->hook !== $this->hook_name) {
      http_response_code(403);
      json_output(json_build(403));
    }

    // Validar que se pase un verbo válido y aceptado
    if(!in_array($this->action, $this->accepted_actions)) {
      http_response_code(403);
      json_output(json_build(403));
    }
    
    // Validación de que todos los parámetros requeridos son proporcionados
    foreach ($this->required_params as $param) {
      if(!isset($this->data[$param])) {
        http_response_code(403);
        json_output(json_build(403));
      }
    }

    // Validar de la petición post / put / delete el token csrf
    if (in_array($this->action, ['post', 'put', 'delete', 'add', 'headers']) && !Csrf::validate($this->csrf)) {
      http_response_code(403);
      json_output(json_build(403));
    }
  }

  function index()
  {
    /**
    200 OK
    201 Created
    300 Multiple Choices
    301 Moved Permanently
    302 Found
    304 Not Modified
    307 Temporary Redirect
    400 Bad Request
    401 Unauthorized
    403 Forbidden
    404 Not Found
    410 Gone
    500 Internal Server Error
    501 Not Implemented
    503 Service Unavailable
    550 Permission denied
    */
    json_output(json_build(403));
  }

  function test()
  {
    try {
      json_output(json_build(200, null, 'Prueba de AJAX realizada con éxito.'));
    } catch (Exception $e) {
      json_output(json_build(400, null, $e->getMessage()));
    }
  }

  ///////////////////////////////////////////////////////
  ///////////////////// PROYECTO DEMO ///////////////////
  ///////////////////////////////////////////////////////
  function bee_add_movement()
  {
    try {
      $mov              = new movementModel();
      $mov->type        = $_POST['type'];
      $mov->description = $_POST['description'];
      $mov->amount      = (float) $_POST['amount'];
      if(!$id = $mov->add()) {
        json_output(json_build(400, null, 'Hubo error al guardar el registro'));
      }
  
      // se guardó con éxito
      $mov->id = $id;
      json_output(json_build(201, $mov->one(), 'Movimiento agregado con éxito'));
      
    } catch (Exception $e) {
      json_output(json_build(400, null, $e->getMessage()));
    }
  }

  function bee_get_movements()
  {
    try {
      $movements          = new movementModel;
      $movs               = $movements->all_by_date();

      $taxes              = (float) get_option('taxes') < 0 ? 16 : get_option('taxes');
      $use_taxes          = get_option('use_taxes') === 'Si' ? true : false;
      
      $total_movements    = $movs[0]['total'];
      $total              = $movs[0]['total_incomes'] - $movs[0]['total_expenses'];
      $subtotal           = $use_taxes ? $total / (1.0 + ($taxes / 100)) : $total;
      $taxes              = $subtotal * ($taxes / 100);
      
      $calculations       = [
        'total_movements' => $total_movements,
        'subtotal'        => $subtotal,
        'taxes'           => $taxes,
        'total'           => $total
      ];

      $data = get_module('movements', ['movements' => $movs, 'cal' => $calculations]);
      json_output(json_build(200, $data));
    } catch(Exception $e) {
      json_output(json_build(400, $e->getMessage()));
    }

  }

  function bee_delete_movement()
  {
    try {
      $mov     = new movementModel();
      $mov->id = $_POST['id'];

      if(!$mov->delete()) {
        json_output(json_build(400, null, 'Hubo error al borrar el registro'));
      }

      json_output(json_build(200, null, 'Movimiento borrado con éxito'));
      
    } catch (Exception $e) {
      json_output(json_build(400, null, $e->getMessage()));
    }
  }

  function bee_update_movement()
  {
    try {
      $movement     = new movementModel;
      $movement->id = $_POST['id'];
      $mov          = $movement->one();

      if(!$mov) {
        json_output(json_build(400, null, 'No existe el movimiento'));
      }

      $data = get_module('updateForm', $mov);
      json_output(json_build(200, $data));
    } catch(Exception $e) {
      json_output(json_build(400, $e->getMessage()));
    }
  }

  function bee_save_movement()
  {
    try {
      $mov              = new movementModel();
      $mov->id          = $_POST['id'];
      $mov->type        = $_POST['type'];
      $mov->description = $_POST['description'];
      $mov->amount      = (float) $_POST['amount'];
      if(!$mov->update()) {
        json_output(json_build(400, null, 'Hubo error al guardar los cambios'));
      }
  
      // se guardó con éxito
      json_output(json_build(200, $mov->one(), 'Movimiento actualizado con éxito'));
      
    } catch (Exception $e) {
      json_output(json_build(400, null, $e->getMessage()));
    }
  }

  function bee_save_options()
  {
    $options =
    [
      'use_taxes' => $_POST['use_taxes'],
      'taxes'     => (float) $_POST['taxes'],
      'coin'      => $_POST['coin']
    ];

    foreach ($options as $k => $option) {
      try {
        if(!$id = optionModel::save($k, $option)) {
          json_output(json_build(400, null, sprintf('Hubo error al guardar la opción %s', $k)));
        }
    
        
      } catch (Exception $e) {
        json_output(json_build(400, null, $e->getMessage()));
      }
    }

    // se guardó con éxito
    json_output(json_build(200, null, 'Opciones actualizadas con éxito'));
  }
  ///////////////////////////////////////////////////////
  /////////////// TERMINA PROYECTO DEMO /////////////////
  ///////////////////////////////////////////////////////

  //Profesores
  function get_materias_disponibles_profesor()
  {
    try {
      if (!check_get_data(['_t', 'id_profesor'], $_GET) || !Csrf::validate($_GET["_t"])) {
        throw new Exception(get_notificaciones());
      }

      $id = clean($_GET["id_profesor"]);

      if (!$profesor = profesorModel::by_id($id)) {
        throw new Exception('No existe el profesor en la base de datos.');
      }

      $materias = materiaModel::disponibles_profesor($profesor['id']);
      json_output(json_build(200, $materias)); 

    } catch(Exception $e) {
      json_output(json_build(400, null, $e->getMessage())); 
    } 
  }

  function get_materias_profesor()
  {
    try {
      if (!check_get_data(['_t', 'id_profesor'], $_GET) || !Csrf::validate($_GET["_t"])) {
        throw new Exception(get_notificaciones());
      }

      $id = clean($_GET["id_profesor"]);

      if (!$profesor = profesorModel::by_id($id)) {
        throw new Exception('No existe el profesor en la base de datos.');
      }

      $materias = materiaModel::materias_profesor($profesor['id']);
      $html     = get_module('profesores/materias', $materias);
      json_output(json_build(200, $html));

    } catch (Exception $e) {
      json_output(json_build(400, null, $e->getMessage()));
    }
  }

  function add_materia_profesor()
  {
    try {
      if (!check_posted_data(['csrf', 'id_profesor', 'id_materia'], $_POST) || !Csrf::validate($_POST["csrf"])) {
        throw new Exception(get_notificaciones());
      }

      $id_materia = clean($_POST["id_materia"]);
      $id_profesor = clean($_POST["id_profesor"]);

      if (!$profesor = profesorModel::by_id($id_profesor)) {
        throw new Exception('No existe el profesor en la base de datos.');
      }

      if (!$materia = materiaModel::by_id($id_materia)) {
        throw new Exception('No existe la materia en la base de datos.');
      }

      // Validar que no este ya asignada la materia 
      if (materiaModel::list('materias_profesores', ['id_materia' => $id_materia, 'id_profesor' => $id_profesor])) {
        throw new Exception(sprintf('La materia <b>%s</b> ya esta asignada al profesor <b>%s</b>.', $materia['nombre'], $profesor['nombres']));
      }

      // Asignar la materia al profesor
      if (profesorModel::asignar_materia($id_profesor, $id_materia) === false) {
        throw new Exception(get_notificaciones(2));
      }

      $msg = sprintf('Nueva materia %s asignada con exito.', $materia['nombre']);

      json_output(json_build(201, $profesor, $msg));

    } catch (Exception $e) {
      json_output(json_build(400, null, $e->getMessage()));
    } catch (PDOException $e) {
      json_output(json_build(400, null, $e->getMessage()));
    }
  }

  function quitar_materia_profesor()
  { 
    try {
      if (!check_posted_data(['csrf', 'id_profesor', 'id_materia'], $_POST) || !Csrf::validate($_POST["csrf"])) {
        throw new Exception(get_notificaciones()); 
      }

    $id_materia = clean($_POST["id_materia"]);
    $id_profesor = clean($_POST["id_profesor"]);

    if (!$profesor = profesorModel::by_id($id_profesor)) {
      throw new Exception('No existe el profesor en la base de datos.'); 
    }

    if (!$materia = materiaModel::by_id($id_materia)) {
      throw new Exception('No existe la materia en la base de datos.');
    }

    // Validar que exista la materia asignada
    if (!materiaModel::list('materias_profesores', ['id_materia' => $id_materia, 'id_profesor' => $id_profesor])) {
      throw new Exception(sprintf('La materia <b>%s</b> no esta asignada al profesor <b>%s</b>', $materia['nombre'], $profesor['nombres']));
    }

    //Quitar materia asignada
    if (profesorModel::quitar_materia($id_profesor, $id_materia) === false) {
      throw new Exception(get_notificaciones(4));
    }

    $msg = sprintf('La materia %s ha sido removida del profesor con exito.', $materia['nombre']);

    json_output(json_build(200, $profesor, $msg));

   } catch(Exception $e) {
      json_output(json_build(400, null, $e->getMessage()));
    } catch(PDOException $e) {
      json_output(json_build(400, null, $e->getMessage())); 
    }
  }

  // Grupos
  function get_materias_disponibles_grupo()
  {
    try {
      if (!check_get_data(['_t', 'id_grupo'], $_GET) || !Csrf::validate($_GET["_t"])) {
        throw new Exception(get_notificaciones());
      }

      $id = clean($_GET["id_grupo"]);

      if (!$grupo = grupoModel::by_id($id)) {
        throw new Exception('No existe el profesor en la base de datos.');
      }

      $materias = grupoModel::materias_disponibles($id);
      json_output(json_build(200, $materias)); 

    } catch(Exception $e) {
      json_output(json_build(400, null, $e->getMessage())); 
    } 
  }

  function get_materias_grupo()
  {
    try {
      if (!check_get_data(['_t', 'id_grupo'], $_GET) || !Csrf::validate($_GET["_t"])) {
        throw new Exception(get_notificaciones());
      }

    $id = clean($_GET["id_grupo"]);

    if (!$grupo = grupoModel::by_id($id)) {
      throw new Exception('No existe el grupo en la base de datos.');
    }

    $materias = grupoModel::materias_asignadas($grupo['id']);
    $html     = get_module('grupos/materias', $materias);
    json_output(json_build(200, $html));

    } catch(Exception $e) {
      json_output(json_build(400, null, $e->getMessage())); 
    }
  }

  function add_materia_grupo()
  {
    try {
      if (!check_posted_data(['csrf', 'id_grupo', 'id_mp'], $_POST) || !Csrf::validate($_POST["csrf"])) {
        throw new Exception(get_notificaciones());
      }

    $id_grupo = clean($_POST["id_grupo"]);
    $id_mp = clean($_POST["id_mp"]);

    if (!$grupo = grupoModel::by_id($id_grupo)) {
      throw new Exception('No existe el grupo en la base de datos.');
    }

    if (!$mp = materiaModel::list('materias_profesores', ['id' => $id_mp])) {
      throw new Exception('No existe la materia en la base de datos.');
    }

    // Validar que no este ya asignada la materia al grupo
    if (grupoModel::list(grupoModel::$t2, ['id_mp' => $id_mp, 'id_grupo' => $id_grupo])) {
      throw new Exception(sprintf('La materia ya esta asignada al grupo <b>%s</b>.', $grupo['nombre']));
    }

    // Asignar la materia al grupo
    if (grupoModel::asignar_materia($id_grupo, $id_mp) === false) {
      throw new Exception(get_notificaciones(2));
    }

    $msg = sprintf('Nueva materia asignada con exito al grupo <b>%s</b>.', $grupo['nombre']);

    json_output(json_build(201, $grupo, $msg));

   } catch(Exception $e) {
    json_output(json_build(400, null, $e->getMessage()));
  } catch(PDOException $e) {
    json_output(json_build(400, null, $e->getMessage()));
  }
  }

  function quitar_materia_grupo()
  {
    try {
      if (!check_posted_data(['csrf', 'id_grupo', 'id_mp'], $_POST) || !Csrf::validate($_POST["csrf"])) {
        throw new Exception(get_notificaciones());
      }

      $id_grupo = clean($_POST["id_grupo"]);
      $id_mp    = clean($_POST["id_mp"]);

      if (!$grupo = grupoModel::by_id($id_grupo)) {
        throw new Exception('No existe el grupo en la base de datos.');
      }
      
      if (!$mp = materiaModel::list('materias_profesores', ['id' => $id_mp])) {
        throw new Exception('No existe la materia en la base de datos.');
      }

      // Validar que exista la materia asignada
      if (!grupoModel::list(grupoModel::$t2, ['id_grupo' => $id_grupo, 'id_mp' => $id_mp])) {
        throw new Exception(sprintf('La materia no esta asignada al grupo <b>%s</b>.', $grupo['nombre']));
      }

      // Quitar materia asignada
      if (grupoModel::quitar_materia($id_grupo, $id_mp) === false) {
        throw new Exception(get_notificaciones(4));
      }

      $msg = sprintf('La materia ha sido removida del grupo <b>%s</b> con exito.', $grupo['nombre']); 
  
      json_output(json_build(200, $grupo, $msg)); 
  
    } catch(Exception $e) {
      json_output(json_build(400, null, $e->getMessage()));
    } catch(PDOException $e) {
      json_output(json_build(400, null, $e->getMessage())); 
    }
  }

  function get_alumnos_grupo()
  {
    try {
      if (!check_get_data(['_t', 'id_grupo'], $_GET) || !Csrf::validate($_GET["_t"])) {
        throw new Exception(get_notificaciones()); 
  }
   
  $id = clean($_GET["id_grupo"]);

  if (!$grupo = grupoModel::by_id($id)) {
    throw new Exception('No existe el grupo en la base de datos.');
  }

  $alumnos  = grupoModel::alumnos_asignados($grupo['id']);
  $html     = get_module('grupos/alumnos', $alumnos);
  json_output(json_build(200, $html));

  } catch(Exception $e) {
    json_output(json_build(400, null, $e->getMessage())); 
}
}

function quitar_alumno_grupo()
{
  try {
    if (!check_posted_data(['csrf', 'id_grupo', 'id_alumno'], $_POST) || !Csrf::validate($_POST["csrf"])) {
      throw new Exception(get_notificaciones());
    }

    $id_grupo   = clean($_POST["id_grupo"]);
    $id_alumno  = clean($_POST["id_alumno"]);

    if (!$grupo = grupoModel::by_id($id_grupo)) {
      throw new Exception('No existe el grupo en la base de datos.');
    }
    
    if (!$alumno = alumnoModel::by_id($id_alumno)) {
      throw new Exception('No existe el alumno en la base de datos.');
    }

    // Validar que exista la alumno asignada
    if (!grupoModel::list(grupoModel::$t3, ['id_grupo' => $id_grupo, 'id_alumno' => $id_alumno])) {
      throw new Exception('El alumno no está inscrito a este grupo.');
    }

    // Quitar alumno asignada
    if (grupoModel::quitar_alumno($id_grupo, $id_alumno) === false) {
      throw new Exception(get_notificaciones(4));
    }

    $msg = sprintf('El alumno ha sido removida del grupo <b>%s</b> con exito.', $grupo['nombre']); 

    json_output(json_build(200, $grupo, $msg)); 

  } catch(Exception $e) {
    json_output(json_build(400, null, $e->getMessage()));
  } catch(PDOException $e) {
    json_output(json_build(400, null, $e->getMessage())); 
  }
}

function suspender_alumno()
{
  try {
    if (!check_posted_data(['csrf', 'id_alumno'], $_POST) || !Csrf::validate($_POST["csrf"])) {
      throw new Exception(get_notificaciones());
    }

    $id_alumno  = clean($_POST["id_alumno"]);
    
    if (!$alumno = alumnoModel::by_id($id_alumno)) {
      throw new Exception('No existe el alumno en la base de datos.');
    }
    
    if ($alumno['status'] === 'suspendido') {
      throw new Exception(sprintf('<b>%s</b> ya se encuentra suspendido.', $alumno['nombre_completo']));
    }

    if ($alumno['status'] === 'pendiente') {
      throw new Exception(sprintf('No se puede suspender al alumno <b>%s</b>.', $alumno['nombre_completo']));
    }

    // Suspensión del alumno
    if (alumnoModel::suspender($id_alumno) === false) {
      throw new Exception(get_notificaciones(4));
    }

    $msg = sprintf('El alumno <b>%s</b> ha sido suspendido con éxito.', $alumno['nombre_completo']); 

    json_output(json_build(200, $alumno, $msg)); 

  } catch(Exception $e) {
    json_output(json_build(400, null, $e->getMessage()));
  } catch(PDOException $e) {
    json_output(json_build(400, null, $e->getMessage())); 
  }
}

function remover_suspension_alumno()
{
  try {
    if (!check_posted_data(['csrf', 'id_alumno'], $_POST) || !Csrf::validate($_POST["csrf"])) {
      throw new Exception(get_notificaciones());
    }

    $id_alumno  = clean($_POST["id_alumno"]);
    
    if (!$alumno = alumnoModel::by_id($id_alumno)) {
      throw new Exception('No existe el alumno en la base de datos.');
    }
    
    if ($alumno['status'] !== 'suspendido') {
      throw new Exception(sprintf('<b>%s</b> no se encuentra suspendido.', $alumno['nombre_completo']));
    }

    if ($alumno['status'] === 'pendiente') {
      throw new Exception(sprintf('No se puede remover la suspensión del alumno <b>%s</b>.', $alumno['nombre_completo']));
    }

    // Retiro de suspensión del alumno
    if (alumnoModel::remover_suspension($id_alumno) === false) {
      throw new Exception(get_notificaciones(4));
    }

    $msg = sprintf('Se ha retirado la suspención del alumno <b>%s</b> con éxito.', $alumno['nombre_completo']);
    $alumno = alumnoModel::by_id($id_alumno);

    json_output(json_build(200, $alumno, $msg)); 

  } catch(Exception $e) {
    json_output(json_build(400, null, $e->getMessage()));
  } catch(PDOException $e) {
    json_output(json_build(400, null, $e->getMessage())); 
  }
}

function get_resumen_comunidad()
{
  try {
    if (!is_admin(get_user_role())) {
      throw new Exception(get_notificaciones(1));
    } 
  
    // Operaciones necesarias
    $stats      = adminModel::stats();
    $comunidad  = $stats['comunidad'];
    $labels     = [];
    $dataset = []; 
    
    foreach ($comunidad as $v) {
      $labels[] = $v['rol'];
      $dataset[] = $v['total'];
    } 
  
    $data = 
    [
      'labels'  => $labels,
      'data'    => $dataset 
    ];

    json_output(json_build(200, $data)); 

  } catch(Exception $e) {
    json_output(json_build(400, null, $e->getMessage()));
  } catch(PDOException $e) {
    json_output(json_build(400, null, $e->getMessage())); 
  }
}

function get_resumen_ensenanza()
{
  try {
    if (!is_admin(get_user_role())) {
      throw new Exception(get_notificaciones(1));
    } 
  
    // Operaciones necesarias
    $stats      = adminModel::stats();
    $ensenanza  = $stats['ensenanza'];
    $labels     = [];
    $dataset = []; 
    
    foreach ($ensenanza as $v) {
      $labels[]   = $v['mes'];
      $dataset[]  = $v['total'];
    } 
  
    $data = 
    [
      'labels'  => $labels,
      'data'    => $dataset 
    ];

    json_output(json_build(200, $data)); 

  } catch(Exception $e) {
    json_output(json_build(400, null, $e->getMessage()));
  } catch(PDOException $e) {
    json_output(json_build(400, null, $e->getMessage())); 
  }
}

}