<?php
use \Verot\Upload\Upload;

/**
 * Plantilla general de controladores
 * Versión 1.0.2
 *
 * Controlador de grupos
 */
class gruposController extends Controller {
  function __construct()
  {
    // Validación de sesión de usuario, descomentar si requerida
    if (!Auth::validate()) {
      Flasher::new('Debes iniciar sesión primero.', 'danger');
      Redirect::to('login');
    }
  }
  
  function index()
  {
    $data = 
    [
      'title'   => 'Todos los grupos',
      'slug'    => 'grupos',
      'button'  => ['url' => 'grupos/agregar', 'text' => '<i class="fas fa-plus"></i> Agregar grupo'],
      'grupos'  => grupoModel::all_paginated()
    ];
    
    // Descomentar vista si requerida
    View::render('index', $data);
  }

  function ver($id)
  {
    if (!is_admin(get_user_role())) {
      Flasher::new(get_notificaciones(), 'danger');
      Redirect::back();
    }

    if (!$grupo = grupoModel::by_id($id)) {
      Flasher::new('No existe el grupo en la base de datos.', 'danger');
      Redirect::back();
    }

    $data =
    [
      'title' => sprintf('Grupo %s', $grupo['nombre']),
      'slug'  => 'grupos',
      'button'=> ['url' => 'grupos', 'text' => '<i class="fas fa-table"></i> Todos los grupos'],
      'g'     => $grupo
    ];

    View::render('ver', $data);
  }

  function agregar()
  {
    $data =
    [
      'title'  => 'Agregar Grupos',
      'button' => ['url' => 'grupos', 'text' => '<i class="fas fa-table"></i> Grupos'],
      'slug'   => 'grupos'
    ];

    View::render('agregar', $data);
  }

  function post_agregar()
  {
    try {
      if(!check_posted_data(['csrf','nombre','descripcion'], $_POST) || !Csrf::validate($_POST['csrf'])) {
        throw new Exception(get_notificaciones());
      }

      //Validar rol
      if (!is_admin(get_user_role())) {
        throw new Exception(get_notificaciones(1));
      }

      $nombre      = clean($_POST["nombre"]);
      $descripcion = clean($_POST["descripcion"]);

      //Validar la longitud del nombre
      if (strlen($nombre) <5) {
        throw new Exception('El nombre del grupo es demasiado corto.');
      }

      // Validar que el nombre del grupo no exista en la base de datos
      $sql = 'SELECT * FROM grupos WHERE nombre = :nombre LIMIT 1';
      if (grupoModel::query($sql, ['nombre' => $nombre])){
        throw new Exception(sprintf('Ya existe el grupo <b>%s</b> en la base de datos.', $nombre));
      }

      $data =
      [
        'numero'      => rand(111111, 999999),
        'nombre'      => $nombre,
        'descripcion' => $descripcion,
        'horario'     => null,
        'creado'      => now()
      ];

      //Insertar a la base de datos
      if(!$id = grupoModel::add(grupoModel::$t1, $data)) {
        throw new Exception(get_notificaciones(2));
      }

      Flasher::new(sprintf('Nuevo grupo <b>%s</b> agregado con éxito.', $nombre), 'success');
      Redirect::back();


    } catch (PDOException $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    } catch (Exception $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    }
  }
  function post_editar()
  {
    try {
      if(!check_posted_data(['csrf', 'id','nombre','descripcion'], $_POST) || !Csrf::validate($_POST['csrf'])) {
        throw new Exception(get_notificaciones());
      }

      //Validar rol
      if (!is_admin(get_user_role())) {
        throw new Exception(get_notificaciones(1));
      }

      $id           = clean($_POST["id"]);
      $nombre       = clean($_POST["nombre"]);
      $descripcion  = clean($_POST["descripcion"]);
      $horario      = $_FILES["horario"];
      $n_horario    = false;
      
      if (!$grupo = grupoModel::by_id($id)){
      throw new Exception('No existe el grupo en la base de dato.');
      }

      $db_horario = $grupo['horario'];

      //Validar la longitud del nombre
      if (strlen($nombre) <5) {
        throw new Exception('El nombre del grupo es demasiado corto.');
      }

      // Validar que el nombre de la materia no exista en la base de datos
      $sql = 'SELECT * FROM grupos WHERE id != :id AND nombre = :nombre LIMIT 1';
      if (grupoModel::query($sql, ['id' => $id, 'nombre' => $nombre])){
        throw new Exception(sprintf('Ya existe el grupo <b>%s</b> en la base de datos.', $nombre));
      }

      $data =
      [
        'nombre'      => $nombre,
        'descripcion' => $descripcion,
      ];

      // Validar si se está subiendo una imagen
      if ($horario['error'] !==4) {
        $tmp  = $horario['tmp_name'];
        $name = $horario['name'];
        $ext  = pathinfo($name, PATHINFO_EXTENSION);

      // Validar extensión del archivo
      if (!in_array($ext, ['jpg','png','jpeg','bmp'])) {
        throw new Exception('Selecciona un formato de imagen válido.');
      }

      $foo = new upload($horario);
      if (!$foo->uploaded) {
        throw new Exception('Hubo un problema al subir el archivo.');
      }

      // Nuevo nombre y nuevas medidas de la imagen
      $filename                 = generate_filename();
      $foo->file_new_name_body  = $filename;
      $foo->image_resize        = true;
      $foo->image_x             = 800;
      $foo->image_ratio_y       = true;

      $foo->process(UPLOADS);
      if (!$foo->processed) {
        throw new Exception('Hubo un problema al guardar la imagen en el servidor.');
      }

      $data['horario'] = sprintf('%s.%s', $filename, $ext);
      $n_horario       = true;
    }

      //Insertar a la base de datos
      if(!grupoModel::update(grupoModel::$t1, ['id' => $id], $data)) {
        throw new Exception(get_notificaciones(3));
      }

      // Borrado del horario anterior en caso de actualización
      if ($db_horario !== null && $n_horario === true && is_file(UPLOADS.$db_horario)) {
        unlink(UPLOADS.$horario);
      }

      Flasher::new(sprintf('Grupo <b>%s</b> actualizada con éxito.', $nombre), 'success');
      Redirect::back();

    } catch (PDOException $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    } catch (Exception $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    }
  }

  function borrar($id)
  {
    try {
      if(!check_get_data(['_t'], $_GET) || !Csrf::validate($_GET['_t'])) {
        throw new Exception(get_notificaciones());
      }

      //Validar rol
      if (!is_admin(get_user_role())) {
        throw new Exception(get_notificaciones(1));
      }

      // Exista el grupo
      if(!$grupo = grupoModel::by_id($id)) {
        throw new Exception('No existe el grupo en la base de datos.');
      }

      // Borramos el registro y sus conexiones
      if (grupoModel::eliminar($grupo['id']) === false) {
        throw new Exception(get_notificaciones(4));
      }

      //Borrar la imagen del horario
      if (is_file(UPLOADS.$grupo['horario'])) {
        unlink(UPLOADS.$grupo['horario']);
      }

      Flasher::new(sprintf('Grupo <b>%s</b> borrado con éxito.', $grupo['nombre']), 'success');
      Redirect::to('grupos');
     
    } catch (PDOException $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    } catch (Exception $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    }
  }

  // Para profesores
  function asignados()
  {
    $data =
    [
      'title'   => 'Grupos Asignados',
      'slug'    => 'grupos',
      'grupos'  => profesorModel::grupos_asignados(get_user('id'))
    ];

    View::render('asignados', $data);
  }

  function detalles($id)
  {
    if (!is_profesor(get_user_role())) {
      Flasher::new(get_notificaciones(), 'danger');
      Redirect::back();
    }

    if (!$grupo = grupoModel::by_id($id)) {
      Flasher::new('No existe el grupo en la base de datos.', 'danger');
      Redirect::back();
    }

    $grupo['materias'] = grupoModel::materias_asignadas($id, get_user('id'));
    $grupo['alumnos'] = grupoModel::alumnos_asignados($id);

    if (!profesorModel::asignado_a_grupo(get_user('id'), $id)) {
    Flasher::new('No eres profesor de este grupo.', 'danger');
    Redirect::to('grupos/asignados');
    }

    $data =
    [
      'title' => sprintf('Grupo %s', $grupo['nombre']),
      'slug'  => 'grupos',
      'button'=> ['url' => 'grupos/asignados', 'text' => '<i class="fas fa-table"></i> Todos mis grupos'],
      'g'     => $grupo
    ];

    View::render('detalles', $data);
  }

  function materia($id)
  {
    if(!is_profesor(get_user_role())) {
      Flasher::new(get_notificaciones(), 'danger');
      Redirect::back();
    }

    if (!$materia = materiaModel::by_id($id)) {
      Flasher::new('No existe la materia en la base de datos.', 'danger');
      Redirect::to('materias');
    }

    $data = 
    [ 
      'title'     => sprintf('Lecciones disponibles para %s', $materia['nombre']),
      'slug'      => 'grupos',
      'button'    => ['url' => 'materias/asignadas', 'text' => '<i class="fas fa-undo"></i> Mis materias'],
      'materia'   => $materia,
      'lecciones' => leccionModel::by_materia_profesor($id, get_user('id')) 
];

View::render('materia', $data); 
}
}