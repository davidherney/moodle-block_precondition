<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'block_precondition', language 'es'
 *
 * @package   block_precondition
 * @copyright 2020 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Mensaje de pre-condición';
$string['precondition:addinstance'] = 'Adicionar un nuevo bloque de Precondición';
$string['precondition:attend'] = 'Atender a una precondición';
$string['goto'] = 'Ir a {$a}';
$string['moduleinfo'] = 'Información del módulo';
$string['moduleinfo_help'] = 'Información acerca del módulo en el que se basa la condición.
La estructura de la información consiste de una cadena JSON válida que contenga un objeto con los campos:  courseid, cmid, name, description, descriptionformat.
Otros parámetros pueden aplicar dependiendo del tipo de módulo.';
$string['not_precondition'] = 'Precondición no configurada';
$string['error/not_user'] = 'Usuario actual no disponible para precondición';
$string['error/cm_error'] = 'Módulo de curso no existe';
$string['error/mod_notimplemented'] = 'El tipo del módulo indicado no está disponible para precondición';
$string['error/bad_json_precondition'] = 'El JSON utilizado para configurar la precondición no es válido';
$string['error/bad_settings_precondition'] = 'El JSON utilizado para configurar la precondición no tiene los campos requeridos';
$string['error/user_notrequire'] = 'El usuario actual no requiere mensaje de precondición.';
$string['satisfied'] = 'Actividad "{$a}" cumplida.';
