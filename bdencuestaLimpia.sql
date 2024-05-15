-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-05-2024 a las 22:42:41
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bdencuesta`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerBottom5PorCargo` (IN `encuesta_id` INT, IN `vinculo_id` INT)   BEGIN
    IF vinculo_id = 0 THEN
        -- Top 5 preguntas por promedio más bajo de todas las respuestas
        SELECT 
             pre.id AS IdPregunta,
            pre.texto AS TextoPregunta,
            ROUND(AVG(r.score), 2) AS PromedioScore,  -- Ya estaba redondeado en esta parte
            COUNT(DISTINCT pr.id) AS cantidad_respuestas
        FROM vinculos v
        LEFT JOIN evaluados ev ON v.id = ev.vinculo_id
        LEFT JOIN personals p ON p.id = ev.evaluador_id
        LEFT JOIN envios e ON e.encuesta = ev.encuesta_id AND e.persona = p.id
        LEFT JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
        LEFT JOIN detalle_preguntas dp ON dp.id = pr.detalle
        LEFT JOIN preguntas pre ON pre.id = dp.pregunta
        LEFT JOIN respuestas r ON r.id = dp.respuesta AND r.estado = 1
        WHERE e.encuesta = encuesta_id AND e.estado = 'F' and r.estado = 1 and r.score != 0
        GROUP BY pre.texto  
        ORDER BY PromedioScore ASC  -- Orden ascendente para obtener los más bajos
        LIMIT 5;
    ELSE
        -- Top 5 preguntas por vínculo específico o todos con los promedios más bajos
        SELECT 
            preg.id AS IdPregunta,
            preg.texto AS TextoPregunta,
            ROUND(AVG(resp.score),2) AS PromedioScore,
            vin.nombre AS NombreVinculo
        FROM 
            persona_respuestas pers_resp
        INNER JOIN 
            detalle_preguntas det_preg ON pers_resp.detalle = det_preg.id
        INNER JOIN 
            preguntas preg ON det_preg.pregunta = preg.id
        INNER JOIN 
            respuestas resp ON det_preg.respuesta = resp.id
        INNER JOIN 
            personals pers ON pers_resp.persona = pers.id
        INNER JOIN
            evaluados ev on ev.evaluador_id = pers.id
        INNER JOIN
            vinculos vin ON vin.id = ev.vinculo_id
        WHERE 
            ev.encuesta_id = encuesta_id AND 
            resp.score !=  0 and
            pers_resp.encuesta_id = encuesta_id AND 
            preg.estado=1 AND
            (vin.id = vinculo_id OR vinculo_id = 0)
        GROUP BY 
            preg.id, preg.texto, vin.nombre
        ORDER BY 
            PromedioScore ASC, NombreVinculo, IdPregunta  -- Orden ascendente aquí también
        LIMIT 5;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerDatosResumen` (IN `encuesta_id` INT)   BEGIN
    SET SESSION group_concat_max_len = 1000000;

    SET @sql = NULL;
    SELECT
        GROUP_CONCAT(DISTINCT
            CONCAT(
                'SUM(CASE WHEN r.texto = ''',
                REPLACE(texto, '''', ''''''),  -- Escapar comillas simples en el texto
                ''' AND r.score != 0 THEN 1 ELSE 0 END) AS `',
                REPLACE(REPLACE(texto, ' ', '_'), '''', ''),  -- Eliminar comillas simples de los nombres de campo
                '`'
            )
       ORDER BY r.score ASC) INTO @sql  -- Ordenar por el campo score
    FROM respuestas r
    left join detalle_preguntas dp on dp.respuesta = r.id
    left join formularios form on form.detalle_id = dp.id
    left join encuestas enc on enc.formulario_id = form.id

    WHERE r.estado = 1 AND r.score != 0 and enc.id = encuesta_id;

    -- Crear la consulta completa utilizando las columnas dinámicas
    SET @sql = CONCAT('SELECT 
        COALESCE(v.nombre, ''Your Average'') AS nombre_vinculo,
        COALESCE(ROUND(AVG(r.score), 2),0) AS promedio_score,
        COUNT(distinct pr.id) AS cantidad_respuestas,
        ', @sql, ' 
      FROM vinculos v
      LEFT JOIN evaluados ev ON v.id = ev.vinculo_id
      LEFT JOIN personals p ON p.id = ev.evaluador_id
      LEFT JOIN envios e ON e.encuesta = ev.encuesta_id AND e.persona = p.id
      LEFT JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
      LEFT JOIN detalle_preguntas dp ON dp.id = pr.detalle
      LEFT JOIN preguntas pre ON pre.id = dp.pregunta
      LEFT JOIN respuestas r ON r.id = dp.respuesta AND r.estado = 1
      WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F" AND r.score != 0 AND r.estado = 1
      GROUP BY v.nombre WITH ROLLUP
      UNION ALL
      SELECT ''Group Average'' AS nombre_vinculo,
         COALESCE(ROUND(AVG(r.score), 2),0) AS promedio_score,
        COUNT(distinct pr.id) AS cantidad_respuestas,
        ', @sql, '
      FROM vinculos v
      LEFT JOIN evaluados ev ON v.id = ev.vinculo_id
      LEFT JOIN personals p ON p.id = ev.evaluador_id
      LEFT JOIN envios e ON e.encuesta = ev.encuesta_id AND e.persona = p.id
      LEFT JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
      LEFT JOIN detalle_preguntas dp ON dp.id = pr.detalle
      LEFT JOIN preguntas pre ON pre.id = dp.pregunta
      LEFT JOIN respuestas r ON r.id = dp.respuesta AND r.estado = 1
      WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F" AND r.score != 0 AND r.estado = 1
      GROUP BY ''Group Average''
    ');

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerResumenEnviosPorCategoria` (IN `categoria_id` INT, IN `encuesta_id` INT)   BEGIN
    SET SESSION group_concat_max_len = 1000000;

    -- Crear la consulta para columnas de respuestas ordenadas por score
    SET @sql = NULL;
    SELECT
      GROUP_CONCAT(DISTINCT
        CONCAT(
                'SUM(CASE WHEN r.texto = ''',
                REPLACE(texto, '''', ''''''),  -- Escapar comillas simples en el texto
                '''  THEN 1 ELSE 0 END) AS `',
                REPLACE(REPLACE(texto, ' ', '_'), '''', ''),  -- Eliminar comillas simples de los nombres de campo
                '`'
            )
      ORDER BY r.score ASC) INTO @sql  -- Ordenar por el campo score
    FROM respuestas r
    left join detalle_preguntas dp on dp.respuesta = r.id
    left join formularios form on form.detalle_id = dp.id
    left join encuestas enc on enc.formulario_id = form.id

    WHERE r.estado = 1 AND r.score != 0 and enc.id = encuesta_id;

    -- Crear la consulta completa con columnas dinámicas
    SET @sql = CONCAT('SELECT 
        COALESCE(v.nombre, ''Your Average'') AS nombre_vinculo,
        COALESCE(ROUND(AVG(r.score), 2),0) AS promedio_score,
        COUNT(distinct pr.id) AS cantidad_respuestas,
        ', @sql, ' 
      FROM vinculos v
      LEFT JOIN evaluados ev ON v.id = ev.vinculo_id
      LEFT JOIN personals p ON p.id = ev.evaluador_id
      LEFT JOIN envios e ON e.encuesta = ev.encuesta_id AND e.persona = p.id
      LEFT JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
      LEFT JOIN detalle_preguntas dp ON dp.id = pr.detalle
	  LEFT JOIN preguntas pre ON pre.id = dp.pregunta
      LEFT JOIN respuestas r ON r.id = dp.respuesta 
      WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F" AND r.score != 0 and r.estado = 1 AND pre.categoria = ', categoria_id, ' 
      GROUP BY v.nombre WITH ROLLUP
      UNION ALL
      SELECT ''Group Average'' AS nombre_vinculo,
        COALESCE(ROUND(AVG(r.score), 2),0) AS promedio_score,
        COUNT(distinct pr.id) AS cantidad_respuestas,
        ', @sql, '
      FROM vinculos v
      LEFT JOIN evaluados ev ON v.id = ev.vinculo_id
      LEFT JOIN personals p ON p.id = ev.evaluador_id
      LEFT JOIN envios e ON e.encuesta = ev.encuesta_id AND e.persona = p.id
      LEFT JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
      LEFT JOIN detalle_preguntas dp ON dp.id = pr.detalle
      LEFT JOIN preguntas pre ON pre.id = dp.pregunta
      LEFT JOIN respuestas r ON r.id = dp.respuesta
      WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F" AND r.score != 0 and r.estado = 1 AND pre.categoria = ', categoria_id, '
      GROUP BY ''Group  Average''
    ');

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerResumenEnviosPorPregunta` (IN `encuesta_id` INT, IN `pregunta_id` INT)   BEGIN
        SET SESSION group_concat_max_len = 1000000;
    SET @sql = NULL;
    
SELECT
    GROUP_CONCAT(DISTINCT
        CONCAT(
            'SUM(CASE WHEN r.texto = ''',
            REPLACE(texto, '''', ''''''),  -- Escape internal single quotes in the text
            ''' AND r.score != 0 THEN 1 ELSE 0 END) AS `',
            REPLACE(REPLACE(texto, ' ', '_'), '''', ''),  -- Remove single quotes from field names
            '`'
            )
      ORDER BY r.score ASC) INTO @sql  -- Ordenar por el campo score
    FROM respuestas r
    left join detalle_preguntas dp on dp.respuesta = r.id
    left join formularios form on form.detalle_id = dp.id
    left join encuestas enc on enc.formulario_id = form.id

    WHERE r.estado = 1 AND r.score != 0 and enc.id = encuesta_id;


    SET @sql = CONCAT('SELECT 
        COALESCE(v.nombre, ''Your Average'') AS nombre_vinculo,
        COALESCE(ROUND(AVG(r.score), 2),0) AS promedio_score,
        COUNT(distinct pr.id) AS cantidad_respuestas,
        ', @sql, ' 
      FROM vinculos v
      LEFT JOIN evaluados ev ON v.id = ev.vinculo_id
      LEFT JOIN personals p ON p.id = ev.evaluador_id
      LEFT JOIN envios e ON e.encuesta = ev.encuesta_id AND e.persona = p.id
      LEFT JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
      LEFT JOIN detalle_preguntas dp ON dp.id = pr.detalle
      LEFT JOIN preguntas pre ON pre.id = dp.pregunta
      LEFT JOIN respuestas r ON r.id = dp.respuesta AND r.estado = 1
      WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F" AND r.score != 0 and r.estado = 1 AND pre.id = ', pregunta_id, ' 
      GROUP BY v.nombre WITH ROLLUP
      UNION ALL
      SELECT ''Group Average'' AS nombre_vinculo,
        COALESCE(ROUND(AVG(r.score), 2),0) AS promedio_score,
        COUNT(distinct pr.id) AS cantidad_respuestas,
        ', @sql, '
      FROM vinculos v
      LEFT JOIN evaluados ev ON v.id = ev.vinculo_id
      LEFT JOIN personals p ON p.id = ev.evaluador_id
      LEFT JOIN envios e ON e.encuesta = ev.encuesta_id AND e.persona = p.id
      LEFT JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
      LEFT JOIN detalle_preguntas dp ON dp.id = pr.detalle
      LEFT JOIN preguntas pre ON pre.id = dp.pregunta
      LEFT JOIN respuestas r ON r.id = dp.respuesta AND r.estado = 1
      WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F"  AND r.score != 0 and r.estado = 1 AND pre.id = ', pregunta_id, '
      GROUP BY ''Group Average''
    ');

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerTop5PorCargo` (IN `encuesta_id` INT, IN `vinculo_id` INT)   BEGIN
    IF vinculo_id = 0 THEN
        -- Top 5 preguntas por promedio general de todas las respuestas
        SELECT 
             pre.id AS IdPregunta,
            pre.texto AS TextoPregunta,
            ROUND(AVG(r.score), 2) AS PromedioScore,  -- Ya estaba redondeado en esta parte
            COUNT(DISTINCT pr.id) AS cantidad_respuestas
        FROM vinculos v
        LEFT JOIN evaluados ev ON v.id = ev.vinculo_id
        LEFT JOIN personals p ON p.id = ev.evaluador_id
        LEFT JOIN envios e ON e.encuesta = ev.encuesta_id AND e.persona = p.id
        LEFT JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
        LEFT JOIN detalle_preguntas dp ON dp.id = pr.detalle
        LEFT JOIN preguntas pre ON pre.id = dp.pregunta
        LEFT JOIN respuestas r ON r.id = dp.respuesta AND r.estado = 1
        WHERE e.encuesta = encuesta_id AND e.estado = 'F' and r.estado = 1 and r.score != 0
        GROUP BY pre.texto  
        ORDER BY PromedioScore DESC
        LIMIT 5;
    ELSE
        -- Top 5 preguntas por vínculo específico o todos si vinculo_id es cero
        SELECT 
            preg.id AS IdPregunta,
            preg.texto AS TextoPregunta,
            ROUND(AVG(resp.score),2) AS PromedioScore,
            vin.nombre AS NombreVinculo
        FROM 
            persona_respuestas pers_resp
        INNER JOIN 
            detalle_preguntas det_preg ON pers_resp.detalle = det_preg.id
        INNER JOIN 
            preguntas preg ON det_preg.pregunta = preg.id
        INNER JOIN 
            respuestas resp ON det_preg.respuesta = resp.id
        INNER JOIN 
            personals pers ON pers_resp.persona = pers.id
        INNER JOIN
            evaluados ev on ev.evaluador_id = pers.id
        INNER JOIN
            vinculos vin ON vin.id = ev.vinculo_id
        WHERE 
            ev.encuesta_id = encuesta_id AND 
            pers_resp.encuesta_id = encuesta_id AND 
            resp.score != 0 and
            preg.estado=1 AND
            (vin.id = vinculo_id OR vinculo_id = 0)
        GROUP BY 
            preg.id, preg.texto, vin.nombre
        ORDER BY 
            PromedioScore DESC, NombreVinculo, IdPregunta
            LIMIT 5;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` text NOT NULL,
  `descripcion` varchar(999) DEFAULT NULL,
  `vigencia` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `vigencia`) VALUES
(1, 'Colaboración', NULL, 1),
(2, 'Resolución de Problemas', NULL, 1),
(3, 'Comunicación', NULL, 1),
(4, 'Adaptabilidad', NULL, 1),
(5, 'Servicio al cliente', NULL, 1),
(7, 'Gestión del tiempo', 'Organizar tareas y cumplir con plazos.', 1),
(8, 'Creatividad', 'Los desarrolladores utilizan estrategias creativas para diseñar tecnologías atractivas para los usuarios y satisfacer las necesidades de los clientes. La creatividad también ayuda a destacar en un mercado competitivo', 1),
(9, 'Comunicación Interpersonal', 'Adaptar tu estilo de comunicación según la audiencia (por ejemplo, usar un lenguaje técnico con colegas y términos más sencillos con clientes).', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cruds`
--

CREATE TABLE `cruds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `model` varchar(191) NOT NULL,
  `route` varchar(191) NOT NULL,
  `icon` varchar(191) NOT NULL DEFAULT 'fas fa-bars',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `built` tinyint(1) NOT NULL DEFAULT 0,
  `with_acl` tinyint(1) NOT NULL DEFAULT 0,
  `with_policy` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cruds`
--

INSERT INTO `cruds` (`id`, `name`, `model`, `route`, `icon`, `active`, `built`, `with_acl`, `with_policy`, `created_at`, `updated_at`) VALUES
(1, 'Empresa', 'App\\Models\\Empresa', 'empresa', 'fa fa-building', 1, 1, 1, 1, '2024-03-20 07:56:59', '2024-04-28 09:46:30'),
(4, 'Personal', 'App\\Models\\Personal', 'personal', 'fa fa-user', 1, 1, 1, 1, '2024-03-20 12:17:15', '2024-05-01 15:24:29'),
(5, 'Formulario', 'App\\Models\\Formulario', 'formulario', 'fa fa-align-left', 1, 1, 1, 1, '2024-04-28 10:12:53', '2024-04-28 10:17:03'),
(8, 'Respuesta', 'App\\Models\\Respuesta', 'respuesta', 'fa fa-comments', 1, 1, 1, 1, '2024-03-22 11:29:00', '2024-04-27 08:52:51'),
(9, 'Pregunta', 'App\\Models\\Pregunta', 'pregunta', 'fa fa-question', 1, 1, 1, 1, '2024-03-22 11:41:04', '2024-04-27 08:52:51'),
(11, 'Encuesta', 'App\\Models\\Encuesta', 'encuesta', 'fa fa-file', 0, 1, 1, 1, '2024-03-23 10:07:55', '2024-05-07 07:20:22'),
(12, 'Envio', 'App\\Models\\Envio', 'envio', 'fa fa-paper-plane', 0, 1, 1, 1, '2024-03-26 09:51:02', '2024-05-07 13:26:42'),
(13, 'Categoria', 'App\\Models\\Categoria', 'categoria', 'fa fa-bars', 1, 1, 1, 1, '2024-03-28 11:00:07', '2024-05-09 15:43:55'),
(14, 'Vinculo', 'App\\Models\\Vinculo', 'vinculo', 'fa fa-link', 1, 1, 1, 1, '2024-04-07 02:08:34', '2024-04-27 08:52:51'),
(15, 'User', 'App\\Models\\User', 'users', 'fa fa-user', 1, 1, 1, 1, '2024-04-22 11:03:58', '2024-05-07 13:15:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_empresas`
--

CREATE TABLE `detalle_empresas` (
  `personal_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_preguntas`
--

CREATE TABLE `detalle_preguntas` (
  `id` int(11) NOT NULL,
  `pregunta` int(11) NOT NULL,
  `respuesta` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_preguntas`
--

INSERT INTO `detalle_preguntas` (`id`, `pregunta`, `respuesta`) VALUES
(11, 31, 1),
(12, 31, 2),
(13, 31, 3),
(14, 31, 4),
(15, 31, 5),
(154, 31, 96),
(269, 31, 166),
(16, 32, 1),
(17, 32, 2),
(18, 32, 3),
(19, 32, 4),
(20, 32, 5),
(155, 32, 96),
(270, 32, 166),
(21, 33, 1),
(22, 33, 2),
(23, 33, 3),
(24, 33, 4),
(25, 33, 5),
(156, 33, 96),
(271, 33, 166),
(26, 34, 1),
(27, 34, 2),
(28, 34, 3),
(29, 34, 4),
(30, 34, 5),
(157, 34, 96),
(272, 34, 166),
(31, 35, 1),
(32, 35, 2),
(33, 35, 3),
(34, 35, 4),
(35, 35, 5),
(158, 35, 96),
(273, 35, 166),
(36, 36, 1),
(37, 36, 2),
(38, 36, 3),
(39, 36, 4),
(40, 36, 5),
(159, 36, 96),
(274, 36, 166),
(41, 37, 1),
(42, 37, 2),
(43, 37, 3),
(44, 37, 4),
(45, 37, 5),
(160, 37, 96),
(275, 37, 166),
(46, 38, 1),
(47, 38, 2),
(48, 38, 3),
(49, 38, 4),
(50, 38, 5),
(161, 38, 96),
(276, 38, 166),
(51, 39, 1),
(52, 39, 2),
(53, 39, 3),
(54, 39, 4),
(55, 39, 5),
(162, 39, 96),
(277, 39, 166),
(56, 40, 1),
(57, 40, 2),
(58, 40, 3),
(59, 40, 4),
(60, 40, 5),
(163, 40, 96),
(278, 40, 166),
(61, 41, 1),
(62, 41, 2),
(63, 41, 3),
(64, 41, 4),
(65, 41, 5),
(164, 41, 96),
(279, 41, 166),
(66, 42, 1),
(67, 42, 2),
(68, 42, 3),
(69, 42, 4),
(70, 42, 5),
(165, 42, 96),
(280, 42, 166),
(71, 43, 1),
(72, 43, 2),
(73, 43, 3),
(74, 43, 4),
(75, 43, 5),
(166, 43, 96),
(281, 43, 166),
(76, 44, 1),
(77, 44, 2),
(78, 44, 3),
(79, 44, 4),
(80, 44, 5),
(167, 44, 96),
(282, 44, 166),
(81, 45, 1),
(82, 45, 2),
(83, 45, 3),
(84, 45, 4),
(85, 45, 5),
(168, 45, 96),
(283, 45, 166),
(91, 49, NULL),
(101, 49, 47),
(109, 49, 50),
(112, 49, 53),
(115, 49, 56),
(118, 49, 59),
(121, 49, 62),
(124, 49, 65),
(127, 49, 68),
(130, 49, 71),
(133, 49, 74),
(136, 49, 77),
(139, 49, 80),
(142, 49, 83),
(145, 49, 86),
(148, 49, 89),
(151, 49, 92),
(170, 49, 97),
(205, 49, 102),
(225, 49, 122),
(228, 49, 125),
(231, 49, 128),
(236, 49, 133),
(237, 49, 134),
(240, 49, 137),
(243, 49, 140),
(246, 49, 143),
(249, 49, 146),
(252, 49, 149),
(254, 49, 151),
(257, 49, 154),
(260, 49, 157),
(263, 49, 160),
(266, 49, 163),
(390, 49, 167),
(393, 49, 170),
(396, 49, 173),
(399, 49, 176),
(92, 50, NULL),
(102, 50, 48),
(110, 50, 51),
(113, 50, 54),
(116, 50, 57),
(119, 50, 60),
(122, 50, 63),
(125, 50, 66),
(128, 50, 69),
(131, 50, 72),
(134, 50, 75),
(137, 50, 78),
(140, 50, 81),
(143, 50, 84),
(146, 50, 87),
(149, 50, 90),
(152, 50, 93),
(171, 50, 98),
(206, 50, 103),
(208, 50, 105),
(209, 50, 106),
(210, 50, 107),
(211, 50, 108),
(212, 50, 109),
(213, 50, 110),
(214, 50, 111),
(215, 50, 112),
(216, 50, 113),
(217, 50, 114),
(218, 50, 115),
(219, 50, 116),
(220, 50, 117),
(221, 50, 118),
(222, 50, 119),
(223, 50, 120),
(224, 50, 121),
(226, 50, 123),
(229, 50, 126),
(232, 50, 129),
(234, 50, 131),
(238, 50, 135),
(241, 50, 138),
(244, 50, 141),
(247, 50, 144),
(250, 50, 147),
(255, 50, 152),
(258, 50, 155),
(261, 50, 158),
(264, 50, 161),
(267, 50, 164),
(391, 50, 168),
(394, 50, 171),
(397, 50, 174),
(400, 50, 177),
(93, 51, NULL),
(103, 51, 49),
(111, 51, 52),
(114, 51, 55),
(117, 51, 58),
(120, 51, 61),
(123, 51, 64),
(126, 51, 67),
(129, 51, 70),
(132, 51, 73),
(135, 51, 76),
(138, 51, 79),
(141, 51, 82),
(144, 51, 85),
(147, 51, 88),
(150, 51, 91),
(153, 51, 94),
(172, 51, 99),
(207, 51, 104),
(227, 51, 124),
(230, 51, 127),
(233, 51, 130),
(235, 51, 132),
(239, 51, 136),
(242, 51, 139),
(245, 51, 142),
(248, 51, 145),
(251, 51, 148),
(253, 51, 150),
(256, 51, 153),
(259, 51, 156),
(262, 51, 159),
(265, 51, 162),
(268, 51, 165),
(392, 51, 169),
(395, 51, 172),
(398, 51, 175),
(401, 51, 178),
(104, 52, 1),
(105, 52, 2),
(106, 52, 3),
(107, 52, 4),
(108, 52, 5),
(169, 52, 96),
(284, 52, 166),
(285, 53, 1),
(286, 53, 2),
(287, 53, 3),
(288, 53, 4),
(289, 53, 5),
(290, 53, 96),
(291, 53, 166),
(292, 54, 1),
(293, 54, 2),
(294, 54, 3),
(295, 54, 4),
(296, 54, 5),
(297, 54, 96),
(298, 54, 166),
(299, 55, 1),
(300, 55, 2),
(301, 55, 3),
(302, 55, 4),
(303, 55, 5),
(304, 55, 96),
(305, 55, 166),
(306, 56, 1),
(307, 56, 2),
(308, 56, 3),
(309, 56, 4),
(310, 56, 5),
(311, 56, 96),
(312, 56, 166),
(313, 57, 1),
(314, 57, 2),
(315, 57, 3),
(316, 57, 4),
(317, 57, 5),
(318, 57, 96),
(319, 57, 166),
(320, 58, 1),
(321, 58, 2),
(322, 58, 3),
(323, 58, 4),
(324, 58, 5),
(325, 58, 96),
(326, 58, 166),
(327, 59, 1),
(328, 59, 2),
(329, 59, 3),
(330, 59, 4),
(331, 59, 5),
(332, 59, 96),
(333, 59, 166),
(334, 60, 1),
(335, 60, 2),
(336, 60, 3),
(337, 60, 4),
(338, 60, 5),
(339, 60, 96),
(340, 60, 166),
(341, 61, 1),
(342, 61, 2),
(343, 61, 3),
(344, 61, 4),
(345, 61, 5),
(346, 61, 96),
(347, 61, 166),
(348, 62, 1),
(349, 62, 2),
(350, 62, 3),
(351, 62, 4),
(352, 62, 5),
(353, 62, 96),
(354, 62, 166),
(355, 63, 1),
(356, 63, 2),
(357, 63, 3),
(358, 63, 4),
(359, 63, 5),
(360, 63, 96),
(361, 63, 166),
(362, 64, 1),
(363, 64, 2),
(364, 64, 3),
(365, 64, 4),
(366, 64, 5),
(367, 64, 96),
(368, 64, 166),
(369, 65, 1),
(370, 65, 2),
(371, 65, 3),
(372, 65, 4),
(373, 65, 5),
(374, 65, 96),
(375, 65, 166),
(376, 66, 1),
(377, 66, 2),
(378, 66, 3),
(379, 66, 4),
(380, 66, 5),
(381, 66, 96),
(382, 66, 166),
(383, 67, 1),
(384, 67, 2),
(385, 67, 3),
(386, 67, 4),
(387, 67, 5),
(388, 67, 96),
(389, 67, 166);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `ruc` varchar(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `representante` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas`
--

CREATE TABLE `encuestas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `empresa` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `proceso` varchar(555) DEFAULT NULL,
  `formulario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios`
--

CREATE TABLE `envios` (
  `id` int(11) NOT NULL,
  `persona` int(11) DEFAULT NULL,
  `encuesta` int(11) DEFAULT NULL,
  `estado` char(1) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `rango` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluados`
--

CREATE TABLE `evaluados` (
  `id` int(11) NOT NULL,
  `evaluado_id` int(11) NOT NULL,
  `evaluador_id` int(11) NOT NULL,
  `encuesta_id` int(11) DEFAULT NULL,
  `vinculo_id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evaluados`
--

INSERT INTO `evaluados` (`id`, `evaluado_id`, `evaluador_id`, `encuesta_id`, `vinculo_id`, `empresa_id`) VALUES
(187, 235, 235, 305, 1, 22),
(188, 236, 236, 306, 1, 22),
(189, 237, 237, 307, 1, 22),
(190, 238, 238, 308, 1, 22),
(196, 235, 233, 305, 5, 22),
(197, 235, 234, 305, 2, 22),
(198, 235, 238, 305, 6, 22),
(199, 235, 237, 305, 3, 22),
(200, 235, 236, 305, 4, 22),
(201, 236, 233, 306, 2, 22),
(202, 236, 237, 306, 4, 22),
(203, 236, 238, 306, 6, 22),
(204, 236, 234, 306, 3, 22),
(205, 236, 235, 306, 4, 22),
(206, 237, 234, 307, 5, 22),
(207, 237, 235, 307, 3, 22),
(208, 237, 236, 307, 6, 22),
(209, 237, 238, 307, 7, 22),
(210, 237, 233, 307, 2, 22),
(211, 238, 233, 308, 3, 22),
(212, 238, 236, 308, 2, 22),
(213, 238, 234, 308, 6, 22),
(214, 238, 235, 308, 4, 22),
(215, 238, 237, 308, 7, 22),
(397, 43, 43, 310, 1, 12),
(398, 43, 42, 310, 2, 12),
(399, 43, 45, 310, 3, 12),
(400, 43, 47, 310, 8, 12),
(401, 45, 45, 311, 1, 12),
(402, 45, 42, 311, 4, 12),
(403, 45, 43, 311, 8, 12),
(404, 45, 46, 311, 2, 12),
(410, 42, 42, 309, 1, 12),
(411, 42, 43, 309, 3, 12),
(412, 42, 45, 309, 8, 12),
(413, 42, 46, 309, 8, 12),
(414, 42, 42, 312, 1, 12),
(415, 42, 43, 312, 3, 12),
(416, 42, 45, 312, 8, 12),
(417, 42, 46, 312, 8, 12),
(418, 43, 43, 313, 1, 12),
(419, 43, 47, 313, 8, 12),
(420, 43, 45, 313, 3, 12),
(421, 43, 42, 313, 2, 12),
(422, 45, 43, 314, 8, 12),
(423, 45, 46, 314, 2, 12),
(424, 45, 42, 314, 4, 12),
(425, 45, 45, 314, 1, 12),
(427, 42, 42, 412, 1, 12),
(428, 42, 43, 412, 2, 12),
(429, 235, 233, 408, 1, 22),
(430, 235, 234, 408, 3, 22),
(431, 235, 235, 408, 5, 22),
(432, 235, 237, 408, 8, 22),
(433, 233, 233, 407, 1, 22),
(434, 233, 234, 407, 4, 22),
(435, 233, 235, 407, 8, 22),
(440, 240, 239, 410, 2, 23),
(441, 240, 240, 410, 1, 23),
(442, 240, 241, 410, 4, 23),
(443, 233, 233, 411, 1, 22),
(444, 233, 234, 411, 2, 22),
(445, 236, 236, 413, 1, 22),
(446, 236, 233, 413, 3, 22),
(447, 236, 234, 413, 4, 22),
(448, 236, 235, 413, 8, 22),
(449, 235, 235, 414, 1, 22),
(450, 235, 233, 414, 3, 22),
(451, 235, 234, 414, 2, 22),
(452, 235, 236, 414, 4, 22),
(453, 239, 239, 415, 1, 23),
(454, 239, 240, 415, 2, 23),
(455, 238, 233, 419, 2, 22),
(456, 238, 234, 419, 3, 22),
(457, 238, 235, 419, 4, 22),
(458, 238, 238, 419, 1, 22),
(459, 234, 233, 420, 2, 22),
(460, 234, 234, 420, 1, 22),
(461, 234, 235, 420, 5, 22),
(462, 234, 236, 420, 8, 22),
(463, 237, 233, 421, 3, 22),
(464, 237, 234, 421, 5, 22),
(465, 237, 235, 421, 3, 22),
(466, 237, 237, 421, 1, 22),
(469, 42, 42, 423, 1, 12),
(470, 42, 43, 423, 3, 12),
(471, 239, 239, 426, 1, 23),
(472, 239, 240, 426, 2, 23),
(473, 239, 241, 426, 4, 23),
(474, 239, 242, 426, 8, 23),
(475, 240, 239, 425, 2, 23),
(476, 240, 240, 425, 1, 23),
(477, 240, 241, 425, 8, 23),
(478, 240, 242, 425, 4, 23),
(479, 243, 243, 424, 1, 23),
(480, 243, 239, 424, 2, 23),
(481, 243, 240, 424, 4, 23),
(482, 243, 241, 424, 3, 23),
(488, 261, 239, 428, 2, 23),
(489, 261, 261, 428, 1, 23),
(490, 261, 240, 428, 3, 23),
(491, 261, 241, 428, 8, 23),
(492, 261, 242, 428, 4, 23),
(493, 42, 42, 429, 1, 12),
(494, 42, 43, 429, 2, 12),
(495, 42, 45, 429, 4, 12),
(496, 42, 46, 429, 8, 12),
(497, 43, 42, 430, 2, 12),
(498, 43, 43, 430, 1, 12),
(499, 43, 45, 430, 3, 12),
(500, 43, 46, 430, 8, 12),
(501, 46, 46, 431, 1, 12),
(502, 46, 42, 431, 2, 12),
(503, 46, 43, 431, 8, 12),
(504, 46, 45, 431, 4, 12),
(505, 47, 47, 432, 1, 12),
(506, 47, 42, 432, 2, 12),
(507, 47, 43, 432, 3, 12),
(508, 47, 45, 432, 8, 12),
(509, 262, 262, NULL, 1, 24),
(510, 262, 263, NULL, 2, 24),
(511, 262, 264, NULL, 3, 24),
(512, 262, 265, NULL, 4, 24),
(513, 262, 266, NULL, 8, 24),
(514, 262, 267, NULL, 8, 24);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formularios`
--

CREATE TABLE `formularios` (
  `id` int(11) NOT NULL,
  `detalle_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2014_10_12_200000_add_two_factor_columns_to_users_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2024_03_18_235428_create_sessions_table', 1),
(7, '2024_03_20_999999_create_cruds_table_easypanel', 1),
(8, '2024_03_20_999999_create_panel_admins_table_easypanel', 1),
(9, '2024_03_20_999999_create_roles_table', 1),
(10, '2024_03_22_062225_create_respuestas_table', 2),
(11, '2024_03_28_035211_create_categorias_table', 3),
(12, '2024_03_30_032856_updatepreguntas', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `panel_admins`
--

CREATE TABLE `panel_admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `is_superuser` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `panel_admins`
--

INSERT INTO `panel_admins` (`id`, `user_id`, `is_superuser`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2024-03-20 07:56:29', '2024-03-20 07:56:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personals`
--

CREATE TABLE `personals` (
  `id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `cargo` text DEFAULT NULL,
  `estado` varchar(255) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona_respuestas`
--

CREATE TABLE `persona_respuestas` (
  `id` int(11) NOT NULL,
  `persona` int(11) NOT NULL,
  `detalle` int(11) NOT NULL,
  `encuesta_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id` int(11) NOT NULL,
  `texto` varchar(1024) NOT NULL,
  `categoria` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 0,
  `vigencia` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id`, `texto`, `categoria`, `estado`, `vigencia`) VALUES
(31, 'Trabaja bien con los demás y contribuye a los objetivos del equipo', 1, 1, 1),
(32, 'Comparte conocimientos y recursos con los demás', 1, 1, 1),
(33, 'Respeta las opiniones e ideas de los demás', 1, 1, 1),
(34, 'Responde a las preguntas de los clientes de manera oportuna y profesional', 5, 1, 1),
(35, 'Asume la responsabilidad de los problemas de los clientes y los resuelve', 5, 1, 1),
(36, 'Demuestra empatía y paciencia cuando trata con clientes difíciles', 5, 1, 1),
(37, 'Aplica un enfoque sistemático a la resolución de problemas', 2, 1, 1),
(38, 'Piensa de forma creativa para encontrar nuevas soluciones a los problemas', 2, 1, 1),
(39, ' Aprende de sus errores pasados y aplica esas lecciones a la resolución de problemas futuros', 2, 1, 1),
(40, ' Se comunica de forma clara y eficaz con los demás', 3, 1, 1),
(41, 'Proporciona retroalimentación de forma constructiva y respetuosa', 3, 1, 1),
(42, 'Adapta su estilo de comunicación a los distintos públicos', 3, 1, 1),
(43, 'Realiza una amplia gama de tareas y responde a los cambios de dirección y prioridades', 4, 1, 1),
(44, 'Trabaja eficazmente en entornos de trabajo dinámicos y cambiantes', 4, 1, 1),
(45, ' Adapta sus planes u horarios a situaciones cambiantes', 4, 1, 1),
(49, '¿Cuáles crees que son sus principales fortalezas? (2 a 3 fortalezas)', NULL, 0, 1),
(50, '¿Cuáles crees que son sus principales oportunidades de mejora? (2 a 3 oportunidades)', NULL, 0, 1),
(51, '¿Si solo pudiese enfocarse en mejorar una conducta cuál debería ser? ', NULL, 0, 1),
(52, 'Realiza los trabajos con responsabilidad y puntualidad', 5, 1, 0),
(53, '¿El empleado presenta nuevas ideas o soluciones creativas?', 8, 1, 1),
(54, '¿Trabaja bien con otros para desarrollar y expandir las ideas presentadas?', 8, 1, 1),
(55, '¿El empleado busca soluciones fuera de los métodos convencionales?', 8, 1, 1),
(56, '¿El empleado logra transmitir información de manera concisa y sin ambigüedades?', 9, 1, 1),
(57, '¿Se comunica de forma clara y eficaz con los demás?', 9, 1, 1),
(58, '¿Adapta su estilo de comunicación a los distintos públicos?', 9, 1, 1),
(59, '¿El empleado puede determinar las causas fundamentales de un problema?', 2, 1, 1),
(60, '¿El empleado resuelve problemas dentro de plazos razonables?', 2, 1, 1),
(61, '¿Analiza los resultados después de implementar una solución para aprender y mejorar?', 2, 1, 1),
(62, '¿Realiza una amplia gama de tareas y responde a los cambios de dirección y prioridades?', 4, 1, 1),
(63, '¿Adapta sus planes u horarios a situaciones cambiantes?', 4, 1, 1),
(64, '¿Utiliza los errores como oportunidades de aprendizaje?', 4, 1, 1),
(65, '¿El empleado cumple con las fechas límite acordadas?', 7, 1, 1),
(66, '¿Sabe identificar y priorizar las tareas más importantes?', 7, 1, 1),
(67, '¿Minimiza distracciones y se enfoca en las tareas clave?', 7, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `id` int(11) NOT NULL,
  `texto` text NOT NULL,
  `score` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL,
  `vigencia` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `respuestas`
--

INSERT INTO `respuestas` (`id`, `texto`, `score`, `estado`, `vigencia`) VALUES
(1, 'Oportunidad Crítica', 1, 1, 1),
(2, 'Debe Mejorar', 2, 1, 1),
(3, 'Regular', 3, 1, 1),
(4, 'Hábil', 4, 1, 1),
(5, 'Destaca', 5, 1, 1),
(47, 'xdd', NULL, 0, 1),
(48, 'barri', NULL, 0, 1),
(49, 'xvz', NULL, 0, 1),
(50, 'abccc', NULL, 0, 1),
(51, 'varias', NULL, 0, 1),
(52, 'sisisis', NULL, 0, 1),
(53, 'wea', NULL, 0, 1),
(54, 'xdd', NULL, 0, 1),
(55, 'avx', NULL, 0, 1),
(56, 'abc', NULL, 0, 1),
(57, 'abc', NULL, 0, 1),
(58, 'xdy', NULL, 0, 1),
(59, 'sdca', NULL, 0, 1),
(60, 'acds', NULL, 0, 1),
(61, 'acda', NULL, 0, 1),
(62, 'abvc', NULL, 0, 1),
(63, 'xyz', NULL, 0, 1),
(64, 'vbn', NULL, 0, 1),
(65, 'wrevfr', NULL, 0, 1),
(66, 'sdvs', NULL, 0, 1),
(67, 'sdvasd', NULL, 0, 1),
(68, 'weaa', NULL, 0, 1),
(69, 'sisi', NULL, 0, 1),
(70, 'aeaaaa', NULL, 0, 1),
(71, 'df', NULL, 0, 1),
(72, 'sdvwsd', NULL, 0, 1),
(73, 'sadvc', NULL, 0, 1),
(74, 'sddf', NULL, 0, 1),
(75, 'adefa', NULL, 0, 1),
(76, 'abc', NULL, 0, 1),
(77, 'SFDGS', NULL, 0, 1),
(78, 'SFVBSDVS', NULL, 0, 1),
(79, 'SFDVBSFB', NULL, 0, 1),
(80, 'SDGA', NULL, 0, 1),
(81, 'SFBSF', NULL, 0, 1),
(82, 'SDVBFSDB', NULL, 0, 1),
(83, 'XDBFSD', NULL, 0, 1),
(84, 'FBSFDB', NULL, 0, 1),
(85, 'FBFDG', NULL, 0, 1),
(86, 'SDVG', NULL, 0, 1),
(87, 'DVSD', NULL, 0, 1),
(88, 'DVDXS', NULL, 0, 1),
(89, 'SXDFV', NULL, 0, 1),
(90, 'SFDVBSV', NULL, 0, 1),
(91, 'SSFDVBFSDZX', NULL, 0, 1),
(92, 'XFVSDXF', NULL, 0, 1),
(93, 'DFVSXFFV', NULL, 0, 1),
(94, 'FSDGSRF', NULL, 0, 1),
(96, 'No Aplica', 0, 1, 1),
(97, 'wrffdrvs', NULL, 0, 1),
(98, 'sdvs', NULL, 0, 1),
(99, 'sdfvfsds', NULL, 0, 1),
(102, 'aedas', NULL, 0, 1),
(103, 'adcd', NULL, 0, 1),
(104, 'adca', NULL, 0, 1),
(105, 'ssisisi', NULL, 0, 1),
(106, 'wefwe', NULL, 0, 1),
(107, 'sdcsd', NULL, 0, 1),
(108, 'adcd', NULL, 0, 1),
(109, 'sas', NULL, 0, 1),
(110, 'qwdqw', NULL, 0, 1),
(111, 'ewsdcas', NULL, 0, 1),
(112, 'sdfvsf', NULL, 0, 1),
(113, 'acas', NULL, 0, 1),
(114, 'abc', NULL, 0, 1),
(115, 'asdca', NULL, 0, 1),
(116, 'asas', NULL, 0, 1),
(117, 'weaaaa', NULL, 0, 1),
(118, 'si trabaja', NULL, 0, 1),
(119, 'si trabaja', NULL, 0, 1),
(120, 'si trabaja', NULL, 0, 1),
(121, 'si trabaja', NULL, 0, 1),
(122, 'sfvs', NULL, 0, 1),
(123, 'sdsd', NULL, 0, 1),
(124, 'sfvs', NULL, 0, 1),
(125, 'sfvs', NULL, 0, 1),
(126, 'sdsd', NULL, 0, 1),
(127, 'sfvs', NULL, 0, 1),
(128, 'sfvs', NULL, 0, 1),
(129, 'sdsd', NULL, 0, 1),
(130, 'sfvs', NULL, 0, 1),
(131, 'no tiene', NULL, 0, 1),
(132, 'muchas', NULL, 0, 1),
(133, 'xdd', NULL, 0, 1),
(134, 'weaa', NULL, 0, 1),
(135, 'sisisi', NULL, 0, 1),
(136, 'eomaodnmc', NULL, 0, 1),
(137, 'weaa', NULL, 0, 1),
(138, 'sisisi', NULL, 0, 1),
(139, 'eomaodnmc', NULL, 0, 1),
(140, 'ya pe mano', NULL, 0, 1),
(141, 'xdddd', NULL, 0, 1),
(142, 'respuesta32', NULL, 0, 1),
(143, 'varias', NULL, 0, 1),
(144, 'respuesta 18', NULL, 0, 1),
(145, 'prueba de prueba', NULL, 0, 1),
(146, 'yeaa', NULL, 0, 1),
(147, 'sigg', NULL, 0, 1),
(148, 'si hace', NULL, 0, 1),
(149, 'yas', NULL, 0, 1),
(150, '11', NULL, 0, 1),
(151, 'yas', NULL, 0, 1),
(152, 'proyecto', NULL, 0, 1),
(153, '11', NULL, 0, 1),
(154, 'adcw', NULL, 0, 1),
(155, 'asdfa', NULL, 0, 1),
(156, 'advc', NULL, 0, 1),
(157, 'ese csmr solo wevea', NULL, 0, 1),
(158, 'se la pasa jugando dota', NULL, 0, 1),
(159, 'wevear', NULL, 0, 1),
(160, 'yes', NULL, 0, 1),
(161, 'aja', NULL, 0, 1),
(162, 'lanzala', NULL, 0, 1),
(163, 'ddd', NULL, 0, 1),
(164, 'xddd', NULL, 0, 1),
(165, 'uwu', NULL, 0, 1),
(166, 'Excelente', 5, 1, 0),
(167, 'asca', NULL, 0, 1),
(168, 'asca', NULL, 0, 1),
(169, 'sac', NULL, 0, 1),
(170, 'dtgedd', NULL, 0, 1),
(171, 'edfvdfdf', NULL, 0, 1),
(172, 'vdfv', NULL, 0, 1),
(173, 'asdcfa', NULL, 0, 1),
(174, 'adc', NULL, 0, 1),
(175, 'asca', NULL, 0, 1),
(176, 'wefw', NULL, 0, 1),
(177, 'werfws', NULL, 0, 1),
(178, 'sfvs', NULL, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `permissions` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'a:1:{s:10:\"fullAccess\";i:1;}', '2024-03-20 07:56:29', '2024-03-20 07:56:29'),
(7, 'admin_empresa', 'a:1:{s:14:\"admin.personal\";a:4:{s:4:\"read\";s:1:\"1\";s:6:\"create\";s:1:\"1\";s:6:\"update\";s:1:\"1\";s:6:\"delete\";s:1:\"1\";}}', '2024-05-02 07:25:15', '2024-05-02 08:22:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_user`
--

CREATE TABLE `role_user` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `role_user`
--

INSERT INTO `role_user` (`role_id`, `user_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('4ZgcMVvrNSHHxWawNXWahD8XDMghlrcvvOoKxFoY', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 OPR/109.0.0.0', 'YTo2OntzOjM6InVybCI7YTowOnt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo2OiJfdG9rZW4iO3M6NDA6InBtTlBBTUd0MDA2ZVZmRFd2ZWJpUjJpaEl1QUUxaG5ZRHc5c25VYWMiO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoyMToicGFzc3dvcmRfaGFzaF9zYW5jdHVtIjtzOjYwOiIkMnkkMTIkeEc4aFU3dUhsTHhzdFR3dTlOdDdidTBwL25jZzV5OTIuRVIweWg0dkNnQXBYVlJyOHVjWi4iO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM2OiJodHRwOi8vbG9jYWxob3N0L2VtcHJlc2EvcGVyc29uYWwvMjQiO319', 1715804471),
('QEpKSZNOJtLSMjiE3MX1JWqrWUvJsD4wGFuqp6ls', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 OPR/109.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiM1RmcGk5TThSYmZVZlFOMWZKdVdxQ2gzZUhLZE5YYzF2TFJheFNSWiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly9sb2NhbGhvc3QvYWRtaW4vdmluY3VsbyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoyMToicGFzc3dvcmRfaGFzaF9zYW5jdHVtIjtzOjYwOiIkMnkkMTIkeEc4aFU3dUhsTHhzdFR3dTlOdDdidTBwL25jZzV5OTIuRVIweWg0dkNnQXBYVlJyOHVjWi4iO30=', 1715805745);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `current_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `empresa_id`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gmail.com', NULL, NULL, '$2y$12$xG8hU7uHlLxstTwu9Nt7bu0p/ncg5y92.ER0yh4vCgApXVRr8ucZ.', NULL, NULL, NULL, 'BQOxVTgbUaGajbTo8NXrGNZQJUAqAXptas9Icjpo147iS5bjgP5xaqeLuYiR', NULL, NULL, '2024-03-20 07:10:54', '2024-03-20 07:10:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vinculos`
--

CREATE TABLE `vinculos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `vigencia` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vinculos`
--

INSERT INTO `vinculos` (`id`, `nombre`, `vigencia`) VALUES
(1, 'Auto Evaluación', 1),
(2, 'Reporte Directo', 1),
(3, 'Par', 1),
(4, 'Cliente', 1),
(5, 'Colega', 1),
(6, 'Jefe Directo', 0),
(7, 'Jefe Matricial', 0),
(8, 'Jefe', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cruds`
--
ALTER TABLE `cruds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cruds_name_unique` (`name`),
  ADD UNIQUE KEY `cruds_model_unique` (`model`),
  ADD UNIQUE KEY `cruds_route_unique` (`route`);

--
-- Indices de la tabla `detalle_empresas`
--
ALTER TABLE `detalle_empresas`
  ADD PRIMARY KEY (`personal_id`,`empresa_id`),
  ADD KEY `fk_empresa` (`empresa_id`),
  ADD KEY `fk_personal_id` (`personal_id`) USING BTREE;

--
-- Indices de la tabla `detalle_preguntas`
--
ALTER TABLE `detalle_preguntas`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `respuesta` (`respuesta`),
  ADD KEY `pregunta` (`pregunta`,`respuesta`,`id`) USING BTREE;

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa` (`empresa`),
  ADD KEY `fk_formulario_id` (`formulario_id`);

--
-- Indices de la tabla `envios`
--
ALTER TABLE `envios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `persona_id` (`persona`),
  ADD KEY `envios_ibfk_2` (`encuesta`);

--
-- Indices de la tabla `evaluados`
--
ALTER TABLE `evaluados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluado_id` (`evaluado_id`),
  ADD KEY `evaluador_id` (`evaluador_id`),
  ADD KEY `encuesta_id` (`encuesta_id`),
  ADD KEY `vinculo_id` (`vinculo_id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `formularios`
--
ALTER TABLE `formularios`
  ADD PRIMARY KEY (`id`,`detalle_id`),
  ADD KEY `detalle_id` (`detalle_id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `panel_admins`
--
ALTER TABLE `panel_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `panel_admins_user_id_unique` (`user_id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personals`
--
ALTER TABLE `personals`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `persona_respuestas`
--
ALTER TABLE `persona_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `persona` (`persona`),
  ADD KEY `fk_detalle_id` (`detalle`),
  ADD KEY `encuesta_id` (`encuesta_id`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_categoria` (`categoria`);

--
-- Indices de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `role_user`
--
ALTER TABLE `role_user`
  ADD KEY `role_user_role_id_index` (`role_id`),
  ADD KEY `role_user_user_id_index` (`user_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `vinculos`
--
ALTER TABLE `vinculos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `cruds`
--
ALTER TABLE `cruds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `detalle_preguntas`
--
ALTER TABLE `detalle_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=402;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `envios`
--
ALTER TABLE `envios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `evaluados`
--
ALTER TABLE `evaluados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=515;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `panel_admins`
--
ALTER TABLE `panel_admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `personals`
--
ALTER TABLE `personals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `persona_respuestas`
--
ALTER TABLE `persona_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `vinculos`
--
ALTER TABLE `vinculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_empresas`
--
ALTER TABLE `detalle_empresas`
  ADD CONSTRAINT `fk_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_personal` FOREIGN KEY (`personal_id`) REFERENCES `personals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_preguntas`
--
ALTER TABLE `detalle_preguntas`
  ADD CONSTRAINT `detalle_preguntas_ibfk_1` FOREIGN KEY (`pregunta`) REFERENCES `preguntas` (`id`),
  ADD CONSTRAINT `detalle_preguntas_ibfk_2` FOREIGN KEY (`respuesta`) REFERENCES `respuestas` (`id`);

--
-- Filtros para la tabla `encuestas`
--
ALTER TABLE `encuestas`
  ADD CONSTRAINT `fk_empresa_id` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `fk_formulario_id` FOREIGN KEY (`formulario_id`) REFERENCES `formularios` (`id`);

--
-- Filtros para la tabla `envios`
--
ALTER TABLE `envios`
  ADD CONSTRAINT `envios_ibfk_1` FOREIGN KEY (`persona`) REFERENCES `personals` (`id`),
  ADD CONSTRAINT `envios_ibfk_2` FOREIGN KEY (`encuesta`) REFERENCES `encuestas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `evaluados`
--
ALTER TABLE `evaluados`
  ADD CONSTRAINT `evaluados_ibfk_1` FOREIGN KEY (`evaluado_id`) REFERENCES `personals` (`id`),
  ADD CONSTRAINT `evaluados_ibfk_2` FOREIGN KEY (`evaluador_id`) REFERENCES `personals` (`id`),
  ADD CONSTRAINT `evaluados_ibfk_3` FOREIGN KEY (`encuesta_id`) REFERENCES `encuestas` (`id`),
  ADD CONSTRAINT `evaluados_ibfk_4` FOREIGN KEY (`vinculo_id`) REFERENCES `vinculos` (`id`),
  ADD CONSTRAINT `evaluados_ibfk_5` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Filtros para la tabla `formularios`
--
ALTER TABLE `formularios`
  ADD CONSTRAINT `formularios_ibfk_1` FOREIGN KEY (`detalle_id`) REFERENCES `detalle_preguntas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `panel_admins`
--
ALTER TABLE `panel_admins`
  ADD CONSTRAINT `panel_admins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `persona_respuestas`
--
ALTER TABLE `persona_respuestas`
  ADD CONSTRAINT `fk_detalle_id` FOREIGN KEY (`detalle`) REFERENCES `detalle_preguntas` (`id`),
  ADD CONSTRAINT `persona_respuestas_ibfk_2` FOREIGN KEY (`persona`) REFERENCES `personals` (`id`),
  ADD CONSTRAINT `persona_respuestas_ibfk_3` FOREIGN KEY (`encuesta_id`) REFERENCES `encuestas` (`id`);

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `fk_categoria` FOREIGN KEY (`categoria`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_empresaid` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
