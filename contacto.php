<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

function clean_text(string $value, int $maxLength): string {
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    return mb_substr($value, 0, $maxLength);
}

function response_page(bool $success, string $message): void {
    $title = $success ? 'Solicitud enviada' : 'No hemos podido enviarla';
    $label = $success ? 'Gracias por escribirnos' : 'Ha ocurrido un problema';
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    http_response_code($success ? 200 : 422);
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $title . ' | ROJA Estudio</title><meta name="robots" content="noindex,nofollow"><link rel="stylesheet" href="/styles.css?v=20260818-2"></head><body><main class="form-response"><a class="brand" href="/"><span>ROJA</span><small>ESTUDIO</small></a><section><p class="eyebrow">' . $label . '</p><h1>' . $title . '.</h1><p>' . $safeMessage . '</p><div class="hero-actions"><a class="button button-dark" href="/">Volver a la web</a><a class="text-link" href="mailto:info@rojaestudio.es">info@rojaestudio.es <span>↗</span></a></div></section></main></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response_page(false, 'Accede al formulario desde la página principal.');
}

if (!empty($_POST['website'] ?? '')) {
    response_page(true, 'Hemos recibido tu solicitud y nos pondremos en contacto contigo.');
}

$nombre = clean_text((string)($_POST['nombre'] ?? ''), 120);
$email = trim((string)($_POST['email'] ?? ''));
$telefono = clean_text((string)($_POST['telefono'] ?? ''), 40);
$proyecto = clean_text((string)($_POST['proyecto'] ?? ''), 80);
$localidad = clean_text((string)($_POST['localidad'] ?? ''), 100);
$mensaje = trim(mb_substr((string)($_POST['mensaje'] ?? ''), 0, 4000));
$privacidad = (string)($_POST['privacidad'] ?? '');

if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $proyecto === '' || $mensaje === '' || $privacidad !== 'aceptada') {
    response_page(false, 'Revisa los campos obligatorios y vuelve a intentarlo. También puedes escribirnos directamente a info@rojaestudio.es.');
}

$to = 'info@rojaestudio.es';
$subjectText = 'Nueva solicitud de presupuesto — ' . $proyecto;
$subject = '=?UTF-8?B?' . base64_encode($subjectText) . '?=';
$body = "Nueva solicitud desde rojaestudio.es\n\n"
    . "Nombre: {$nombre}\n"
    . "Email: {$email}\n"
    . "Teléfono: " . ($telefono !== '' ? $telefono : 'No indicado') . "\n"
    . "Tipo de proyecto: {$proyecto}\n"
    . "Localidad: " . ($localidad !== '' ? $localidad : 'No indicada') . "\n\n"
    . "Mensaje:\n{$mensaje}\n";

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: ROJA Estudio <info@rojaestudio.es>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
];

$sent = mail($to, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    response_page(false, 'El servidor no ha podido enviar el mensaje. Escríbenos directamente a info@rojaestudio.es.');
}

response_page(true, 'Hemos recibido la información. Te responderemos para conocer mejor el proyecto y concertar una primera visita.');
