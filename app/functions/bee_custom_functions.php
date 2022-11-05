<?php 

/**
 * Regra el rol de usuario
 * 
 * @return mixed
 */
function get_user_role(){
  return $rol = get_user('rol');
}

function get_default_roles(){
  return ['root', 'admin'];
}

function is_root($rol){
  return in_array($rol, ['root']);
}

function is_admin($rol){
  return in_array($rol, ['admin', 'root']);
}

function is_profesor($rol){
  return in_array($rol, ['profesor', 'admin', 'root']);
}

function is_alumno($rol){
  return in_array($rol, ['alumno','admin', 'root']);
}

function is_user($rol, $roles_aceptados){
  $default = get_default_roles();

  if (!is_array($roles_aceptados)) {
    array_push($default, $roles_aceptados);
    return in_array($rol, $default);
  }

  return in_array($rol, array_merge($default, $roles_aceptados));
}

/**
 * 0 Acceso no autorizado
 * 1 Acción no autorizada
 * 2 agregar
 * 3 actualizar
 * 4 borrar
 * 
 * @param integer $index
 * @return string
 */
function get_notificaciones($index =0)
{
  $notifificaciones=
[
  'Acceso no autorizado.',
  'Acción no autorizado.',
  'Hubo un error al agregar el registro.',
  'Hubo un error al actualizar el registro.',
  'Hubo un error al borrar el registro.',
];

return isset($notifificaciones[$index]) ? $notifificaciones[$index] : $notifificaciones[0];
}

function get_estados_usuarios()
{
  return
  [
    ['pendiente', 'Pendiente de activación'],
    ['activo', 'Activo'],
    ['suspendido', 'Suspendido']
  ];
}

function format_estado_usuario($status)
{
  $placeholder = '<div class="badge %s"><i class="%s"></i> %s</div>';
  $classes  = '';
  $icon     = '';
  $text     = '';

  switch ($status) {
    case 'pendiente':
      $classes  = 'badge-warning text-dark';
      $icon     = 'fas fa-clock';
      $text     = 'Pendiente';
      break;

    case 'activo':
      $classes  = 'badge-success';
      $icon     = 'fas fa-check';
      $text     = 'Activo';
      break;

    case 'suspendido':
      $classes  = 'badge-danger';
      $icon     = 'fas fa-times';
      $text     = 'Suspendido';
      break;

    default:
      $classes  = 'badge-danger';
      $icon     = 'fas fa-question';
      $text     = 'Desconocido';
      break;
  }

  return sprintf($placeholder, $classes, $icon, $text);
}

/**
 * Enviar email de activación de correo electrónico
 * 
 * @param int $id_usuario
 * @return bool
 */
function mail_confirmar_cuenta($id_usuario)
{
  if (!$usuario = usuarioModel::by_id($id_usuario)) return false; // nuevo metodo creado en modelo

  $nombre = $usuario['nombres'];
  $hash   = $usuario['hash'];
  $email  = $usuario['email'];
  $status = $usuario['status'];

  // Si no es pendiente el status no requiere activaciOn
  if ($status !== 'pendiente') return false;

  $subjet = sprintf('Confirma tu correo eletronico por favor %s', $nombre);
  $alt  = sprintf('Debes confirmar tu correo electrónico para poder ingresar a %s.', get_sitename());
  $url  = buildURL(URL.'login/activate', ['email' => $email, 'hash' => $hash], false, false);
  $text  = 'iHola %s!<br>Para ingresar al sistema de <b>%s</b> primero debes confirmar tu direccion de correo electrónico dando clic en el siguiente enlace seguro: <a
  href="%s">%s</a>';
  $body  = sprintf($text, $nombre, get_sitename(), $url, $url);

  // Creando el correo electrónico
  if (send_email(get_siteemail(), $email, $subjet, $body, $alt) !== true) return false;

  return true;
}
  /**
   * Regresa los estados disponibles para una lección
   * 
   * @return array
   */
  function get_estados_lecciones()
  {
    return
    [
      ['borrador', 'Borrador'],
      ['publica', 'Publicada']
    ];
  }

  
  
  
function format_estado_leccion($status)
{
  $placeholder = '<div class="badge %s"><i class="%s"></i> %s</div>';
  $classes  = '';
  $icon     = '';
  $text     = '';

  switch ($status) {
    case 'borrador':
      $classes  = 'badge-info';
      $icon     = 'fas fa-eraser';
      $text     = 'Boorador';
      break;

    case 'publica':
      $classes  = 'badge-success';
      $icon     = 'fas fa-check';
      $text     = 'Publicada';
      break;

    default:
      $classes  = 'badge-danger';
      $icon     = 'fas fa-question';
      $text     = 'Desconocido';
      break;
  }

  return sprintf($placeholder, $classes, $icon, $text);
}

function format_tiempo_restante($fecha)
{
  $output   = '';
  $segundos = strtotime($fecha) - time();
  $segundos = $segundos < 0 ? 0 : $segundos;
  $minutos  = $segundos / 60;
  $horas    = $minutos / 60;
  $dias     = $horas / 24;
  $semanas  = $dias / 7;
  $meses    = $semanas / 4;
  $anos     = $meses / 12;

  switch (true) {
    case $anos >= 1:
      $anos = floor($anos);
      $output = sprintf('%s %s', $anos, $anos === 1 ? 'año restante.' : 'años restantes.');
      break;

    case $meses >= 2:
      $output = sprintf('%s meses restantes.', floor($meses));
      break;

    case $semanas >= 2:
      $output = sprintf('%s semanas restantes.', floor($semanas));
    break;

    case $dias >= 3:
      $output = sprintf('%s dias restantes.', floor($dias));
      break;

    case $horas >= 2:
      $output = sprintf('%s horas restantes.', floor($horas));
      break;

    case $minutos >= 2:
      $output = sprintf('%s minutos restantes.', floor($minutos));
      break; 

    case $segundos > 0:
      $output = sprintf('%s segundos restantes.', $segundos);
      break; 

    case $segundos === 0:
      $output = 'El tiempo de ha terminado.';
      break;

    default:
      $output = 'Desconocido.';
      break;
    }

    return $output;
  } 



function get_ingresos()
{
  return 
  [
    ['Enero'          , 23848],
    ['Febrero'        , 85633],
    ['Marzo'          , 54200],
    ['Abril'          , 61250],
    ['Mayo'           , 12577],
    ['Junio'          , 25966],
    ['Julio'          , 34700],
    ['Agosto'         , 75000],
    ['Septiembre'     , 23848],
    ['Octubre'        , 16450], 
    ['Noviembre'      , 63250],
    ['Diciembre'      , 83500],
  ];
}

function get_proyectos()
{ 
  return 
  [  
    [ 
      'titulo'    => 'Programa escolar 2022',
      'tipo'      => 'danger',
      'progreso'  => rand(0, 70)
    ],
    [
      'titulo'    => 'Registro de nuevos alumnos',
      'tipo'      => 'warning',
      'progreso'  => rand(50, 95)
    ],
    [ 
      'titulo'    => 'Registro de profesores',
      'tipo'      => 'primary',
      'progreso'  => rand(10, 50)
    ] ,
    [ 
      'titulo'    => 'Capacitacion de personal',
      'tipo'      => 'info',
      'progreso'  => rand(95, 100)
    ],
    [ 
      'titulo'    => 'Crear un sistema escolar',
      'tipo'      => 'success',
      'progreso'  => 100 
    ],
  ];
}