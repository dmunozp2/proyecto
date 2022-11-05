<?php

/**
 * Plantilla general de controladores
 * Versión 1.0.2
 *
 * Controlador de alumnos
 */
class alumnosController extends Controller {
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
    if (!is_admin(get_user_role())) {
      Flasher::new(get_notificaciones(), 'danger');
      Redirect::back();
    }

    $data = 
    [
      'title'       => 'Todos los Alumnos',
      'slug'        => 'alumnos',
      'button'      => ['url' => 'alumnos/agregar', 'text' => '<i class="fas fa-plus"></i> Agregar alumno'],
      'alumnos'  => alumnoModel::all_paginated()
    ];
    
    // Descomentar vista si requerida
    View::render('index', $data);
  }

  function ver($id)
  {
    if (!$alumno = alumnoModel::by_id($id)) {
      Flasher::new('No existe el alumno en la base de datos.', 'danger');
      Redirect::back();
    }

    $data =
    [
      'title' => sprintf('Alumno #%s', $alumno['numero']),
      'slug'  => 'alumnos',
      'button'=> ['url' => 'alumnos', 'text' => '<i class="fas fa-table"></i> Alumnos'],
      'grupos'=> grupoModel::all(),
      'a'     => $alumno
    ];

    View::render('ver', $data);
  }

  function agregar()
  {
    $data = 
    [
      'title'    => 'Agregar alumno',
      'slug'     => 'alumnos',
      'button'   => ['url' => 'alumnos', 'text' => '<i class="fas fa-table"></i> Alumnos'],
      'grupos'  => grupoModel::all()
    ];

    View::render('agregar', $data);
  }

  function post_agregar()
  {
    try {
    if (!check_posted_data(['csrf','nombres','apellidos','email','telefono','password','conf_password','id_grupo'], $_POST) || !Csrf::validate($_POST['csrf'])) {
      throw new Exception(get_notificaciones());
    }

    // Validar rol
    if (!is_admin(get_user_role())) {
      throw new Exception(get_notificaciones(1));
    }

      $nombres        = clean($_POST["nombres"]);
      $apellidos      = clean($_POST["apellidos"]);
      $email          = clean($_POST["email"]);
      $telefono       = clean($_POST["telefono"]);
      $password       = clean($_POST["password"]);
      $conf_password  = clean($_POST["conf_password"]);
      $id_grupo       = clean($_POST["id_grupo"]);

      // Validar que el correo sea válido
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Ingresa un correo electrónico válido.');
      }

      // Validar el nombre del usuario
      if (strlen($nombres) < 5) {
        throw new Exception('Ingresa un nombre valido.');
      }

      // Validar el apellido del usuario
      if (strlen($apellidos) < 5) {
        throw new Exception('Ingresa un apellido valido.');
      }

      // Validar el password del usuario
      if (strlen($password) < 5) {
        throw new Exception('Ingresa una contraseha mayor a 5 caracteres.');
      }

      // Validar ambas contrasegas
      if ($password !== $conf_password) {
        throw new Exception('Las contrasñas no son iguales.');
      }

      // Exista el id grupo
      if ($id_grupo === '' || !grupoModel::by_id($id_grupo)) {
        throw new Exception('Selecciona un grupo valido.');
      }

      $data = 
      [
        'numero'          => rand(111111, 999999),
        'nombres'         => $nombres,
        'apellidos'       => $apellidos,
        'nombre_completo' => sprintf('%s %s', $nombres, $apellidos),
        'email'           => $email,
        'telefono'        => $telefono,
        'password'        => password_hash($password.AUTH_SALT, PASSWORD_BCRYPT),
        'hash'            => generate_token(),
        'rol'             => 'alumno',
        'status'          => 'pendiente',
        'creado'          => now()
      ];

      $data2 =
      [
        'id_alumno' => null,
        'id_grupo' => $id_grupo 
      ];
      
      // Insertar a la base de datos
      if (!$id = alumnoModel::add(alumnoModel::$t1, $data)) {
        throw new Exception(get_notificaciones(2));
      }

      $data2['id_alumno'] = $id;

      // Insertar a la base de datos
      if(!$id_ga = grupoModel::add(grupoModel::$t3, $data2)) {
        throw new Exception(get_notificaciones());
      }

      // Email de confirmaciOn de correo mail_confirmar_cuenta($id);
      mail_confirmar_cuenta($id);

      $alumno = alumnoModel::by_id($id);
      $grupo  = grupoModel::by_id($id_grupo);

      Flasher::new(sprintf('Alumno <b>%s</b> agregado con éxito e inscrito al grupo <b>%s</b>.', $alumno['nombre_completo'], $grupo['nombre']), 'success');
      Redirect::back(); 
  
    } catch (PDOException $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    } catch (Exception $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    }
  }

  function editar($id)
  {
    View::render('editar');
  }

  function post_editar()
  {
    try {
      if (!check_posted_data(['csrf','id','nombres','apellidos','email','telefono','password','conf_password','id_grupo'], $_POST) || !Csrf::validate($_POST['csrf'])) {
        throw new Exception(get_notificaciones());
      }

      // Validar rol
      if (!is_admin(get_user_role())) { 
        throw new Exception(get_notificaciones(1));
      } 
       
      // Validar existencia del alumno
      $id = clean($_POST["id"]);
      if (!$alumno = alumnoModel::by_id($id)) {
        throw new Exception('No existe el alumno en la base de datos.');
      }

      $db_email   = $alumno['email'];
      $db_pw      = $alumno['password'];
      $db_status  = $alumno['status'];
      $db_id_g    = $alumno['id_grupo']; 

      $nombres        = clean($_POST["nombres"]);
      $apellidos      = clean($_POST["apellidos"]);
      $email          = clean($_POST["email"]);
      $telefono       = clean($_POST["telefono"]);
      $password       = clean($_POST["password"]);
      $conf_password  = clean($_POST["conf_password"]);
      $id_grupo       = clean($_POST["id_grupo"]);
      $changed_email  = $db_email === $email ? false : true;
      $changed_pw     = false;
      $changed_g      = $db_id_g === $id_grupo ? false : true; 
      
      // Validar existencia del correo electrOnico
      $sql = 'SELECT * FROM usuarios WHERE email = :email AND id != :id LIMIT 1';
      if (usuarioModel::query($sql, ['email' => $email, 'id' => $id])) {
        throw new Exception('El correo electronic° ya existe en la base de datos.');
      }

      // Validar que el correo sea valido
      if ($changed_email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Ingresa un correo electrónico válido.'); 
      }

      // Validar el nombre del usuario
      if (strlen($nombres) < 5) {
        throw new Exception('Ingresa un nombre valido.');
      }

      // Validar el apellido del usuario
      if (strlen($apellidos) < 5) {
        throw new Exception('Ingresa un apellido valido.');
      }

      // Validar el password del usuario
      $pw_ok = password_verify($db_pw, $password.AUTH_SALT);
      if (!empty($password) && $pw_ok === false && strlen($password) < 5) {
        throw new Exception('Ingresa una contraseha mayor a 5 caracteres.');
      }

      // Validar ambas contraseñas
      if(!empty($password) && $pw_ok === false && $password !== $conf_password) {
        throw new Exception('Las contrasehas no son iguales.');
      }

      // Exista el id_grupo
      if ($id_grupo === '' || !grupoModel::by_id($id_grupo)) {
        throw new Exception('Selecciona un grupo valido.');
      }

      $data =
      [
        'nombres'         => $nombres,
        'apellidos'       => $apellidos,
        'nombre_completo' => sprintf('%s %s', $nombres, $apellidos),
        'email'           => $email,
        'telefono'        => $telefono, 
        'status'          => $changed_email ? 'pendiente' : $db_status 
      ]; 

      //ActualizaciOn de contraseña
      if (!empty($password) && $pw_ok === false) {
        $data['password'] = password_hash($password.AUTH_SALT, PASSWORD_BCRYPT);
        $changed_pw = true;
      }

      // Actualizar base de datos
      if (!alumnoModel::update(alumnoModel::$t1, ['id' => $id], $data)) {
        throw new Exception(get_notificaciones(2));
      }

      // Actualizar base de datos
      if ($changed_g) {
        if (!grupoModel::update(grupoModel::$t3, ['id_alumno' => $id], ['id_grupo' => $id_grupo])) {
          throw new Exception(get_notificaciones(2));
        }
      }

    $alumno = alumnoModel::by_id($id);
    $grupo = grupoModel::by_id($id_grupo);

    Flasher::new(sprintf('Alumno <b>%s</b> actualizado con exito.', $alumno['nombre_completo']), 'success');

    if ($changed_email) {
      mail_confirmar_cuenta($id);
      Flasher::new('El correo electronico del alumno ha sido actualizado, debe ser confirmado.');
    }

    if ($changed_pw) {
      Flasher::new('la contraseha del alumno ha sido actualizada.');
    }

    if ($changed_g) {
      Flasher::new(sprintf('El grupo del alumno ha sido actualizado a %s con exito.', $grupo['nombre'])); 
    }

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

      // Exista el elumno
      if(!$alumno = alumnoModel::by_id($id)) {
        throw new Exception('No existe el alumno en la base de datos.');
      }

      // Borramos el registro y sus conexiones
      if (alumnoModel::eliminar($alumno['id']) === false) {
        throw new Exception(get_notificaciones(4));
      }

      Flasher::new(sprintf('Alumno <b>%s</b> borrado con éxito.', $alumno['nombre_completo']), 'success');
      Redirect::to('alumnos');
     
    } catch (PDOException $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    } catch (Exception $e) {
      Flasher::new($e->getMessage(), 'danger');
      Redirect::back();
    }
  }
}
