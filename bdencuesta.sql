-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-05-2024 a las 13:10:56
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
            REPLACE(texto, '''', ''''''),  -- Escape internal single quotes in the text
            ''' AND r.score != 0 THEN 1 ELSE 0 END) AS `',
            REPLACE(REPLACE(texto, ' ', '_'), '''', ''),  -- Remove single quotes from field names
            '`'
        )
  ) INTO @sql
FROM respuestas
WHERE estado = 1 AND id IN (
    SELECT dp.respuesta
    FROM envios e
    JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
    JOIN detalle_preguntas dp ON dp.id = pr.detalle
    JOIN preguntas pre ON pre.id = dp.pregunta
    WHERE e.encuesta = encuesta_id AND e.estado = 'F'
);

SET @sql = CONCAT('SELECT 
    COALESCE(v.nombre, ''Your Average'') AS nombre_vinculo,
    COALESCE(AVG(r.score),0) AS promedio_score,
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
  WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F" and r.estado = 1 and r.score != 0
  GROUP BY v.nombre WITH ROLLUP
  UNION ALL
  SELECT ''Group Average'' AS nombre_vinculo,
    COALESCE(AVG(r.score),0) AS promedio_score,
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
  WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F" and r.estado = 1 and r.score != 0
  GROUP BY ''Group Average''
');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerResumenEnviosPorCategoria` (IN `categoria_id` INT, IN `encuesta_id` INT)   BEGIN
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
  ) INTO @sql
FROM respuestas
WHERE estado = 1 AND id IN (
    SELECT dp.respuesta
    FROM envios e
    JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
    JOIN detalle_preguntas dp ON dp.id = pr.detalle
    JOIN preguntas pre ON pre.id = dp.pregunta
    WHERE e.encuesta = encuesta_id AND e.estado = 'F' AND pre.categoria = categoria_id 
);

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
  WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F"  AND r.score != 0 and r.estado = 1 AND pre.categoria = ', categoria_id, ' 
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
  WHERE e.encuesta = ', encuesta_id, ' AND e.estado = "F"  AND r.score != 0 and r.estado = 1 AND pre.categoria = ', categoria_id, '
  GROUP BY ''Group Average''
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
    ) INTO @sql
    FROM respuestas
    WHERE estado = 1  AND id IN (
        SELECT dp.respuesta
        FROM envios e
        JOIN persona_respuestas pr ON pr.encuesta_id = e.encuesta AND pr.persona = e.persona
        JOIN detalle_preguntas dp ON dp.id = pr.detalle
        JOIN preguntas pre ON pre.id = dp.pregunta
        WHERE e.encuesta = encuesta_id AND e.estado = 'F' AND pre.id = pregunta_id 
    );

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
  `nombre` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Colaboración'),
(2, 'Resolución de Problemas'),
(3, 'Comunicación'),
(4, 'Adaptabilidad'),
(5, 'Servicio al cliente');

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
(13, 'Categoria', 'App\\Models\\Categoria', 'categoria', 'fa fa-bars', 1, 1, 1, 1, '2024-03-28 11:00:07', '2024-04-27 08:52:51'),
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

--
-- Volcado de datos para la tabla `detalle_empresas`
--

INSERT INTO `detalle_empresas` (`personal_id`, `empresa_id`) VALUES
(42, 12),
(43, 12),
(45, 12),
(46, 12),
(47, 12),
(49, 12),
(161, 13),
(167, 14),
(168, 14),
(233, 22),
(234, 22),
(235, 22),
(236, 22),
(237, 22),
(238, 22),
(239, 23),
(240, 23),
(241, 23),
(242, 23),
(243, 23),
(244, 23),
(245, 24),
(259, 12),
(260, 14);

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
(16, 32, 1),
(17, 32, 2),
(18, 32, 3),
(19, 32, 4),
(20, 32, 5),
(155, 32, 96),
(21, 33, 1),
(22, 33, 2),
(23, 33, 3),
(24, 33, 4),
(25, 33, 5),
(156, 33, 96),
(26, 34, 1),
(27, 34, 2),
(28, 34, 3),
(29, 34, 4),
(30, 34, 5),
(157, 34, 96),
(31, 35, 1),
(32, 35, 2),
(33, 35, 3),
(34, 35, 4),
(35, 35, 5),
(158, 35, 96),
(36, 36, 1),
(37, 36, 2),
(38, 36, 3),
(39, 36, 4),
(40, 36, 5),
(159, 36, 96),
(41, 37, 1),
(42, 37, 2),
(43, 37, 3),
(44, 37, 4),
(45, 37, 5),
(160, 37, 96),
(46, 38, 1),
(47, 38, 2),
(48, 38, 3),
(49, 38, 4),
(50, 38, 5),
(161, 38, 96),
(51, 39, 1),
(52, 39, 2),
(53, 39, 3),
(54, 39, 4),
(55, 39, 5),
(162, 39, 96),
(56, 40, 1),
(57, 40, 2),
(58, 40, 3),
(59, 40, 4),
(60, 40, 5),
(163, 40, 96),
(61, 41, 1),
(62, 41, 2),
(63, 41, 3),
(64, 41, 4),
(65, 41, 5),
(164, 41, 96),
(66, 42, 1),
(67, 42, 2),
(68, 42, 3),
(69, 42, 4),
(70, 42, 5),
(165, 42, 96),
(71, 43, 1),
(72, 43, 2),
(73, 43, 3),
(74, 43, 4),
(75, 43, 5),
(166, 43, 96),
(76, 44, 1),
(77, 44, 2),
(78, 44, 3),
(79, 44, 4),
(80, 44, 5),
(167, 44, 96),
(81, 45, 1),
(82, 45, 2),
(83, 45, 3),
(84, 45, 4),
(85, 45, 5),
(168, 45, 96),
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
(104, 52, 1),
(105, 52, 2),
(106, 52, 3),
(107, 52, 4),
(108, 52, 5),
(169, 52, 96);

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

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id`, `ruc`, `nombre`, `direccion`, `representante`, `estado`) VALUES
(12, '20602871119', 'GARZASOFT E.I.R.L.', 'CAL.NICOLAS LA TORRE NRO. 126 URB. MAGISTERIAL LAMBAYEQUE - CHICLAYO - CHICLAYO', 'GUZMAN MORI CARLOS GUSTAVO', 1),
(13, '20100151384', 'SOFTWARE S A', 'AV. MANUEL OLGUIN NRO. 375 INT. 501C URB. LOS GRANADOS LIMA LIMA SANTIAGO DE SURCO', 'OCROSPOMA UGAZ FRANK ANTHONY', 1),
(14, '20513613009', 'SOFTWARE ENTERPRISE SERVICES SOCIEDAD ANONIMA CERRADA', 'JR. SANTA ROSA NRO. 191 INT. 206 LIMA LIMA LIMA', 'PADILLA RIOS JOSE LUIS', 1),
(22, '20611644940', 'TARMA & HERNANDEZ INVERSIONES E.I.R.L.', '---- LAS PAMPAS MZA. U LOTE. 22 H.U. SOL DE PIMENTEL LAMBAYEQUE CHICLAYO PIMENTEL', 'OCROSPOMA UGAZ FRANK ANTHONY', 1),
(23, '20539111702', 'YANSUMI MOTOR EIRL', 'CAL. 6 MZA. X LOTE. 22 OTR. PARQUE INDUSTRIAL EL ASES LIMA LIMA ATE', 'GUZMAN MORI CARLOS GUSTAVO', 1),
(24, '20501781291', 'MEDIC SER S.A.C.', 'AV. REPUBLICA DE PANAMA NRO. 3461 LIMA LIMA SAN ISIDRO', 'LOZANO RUIZ MARIA MILAGROS', 1);

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

--
-- Volcado de datos para la tabla `encuestas`
--

INSERT INTO `encuestas` (`id`, `nombre`, `empresa`, `fecha`, `proceso`, `formulario_id`) VALUES
(305, 'Evaluación a Pedro Jose Suarez', 22, '2024-04-27', 'Primer Proceso', 2),
(306, 'Evaluación a Juan Manuel Perez', 22, '2024-04-27', 'Primer Proceso', 2),
(307, 'Evaluación a Robert Luis Sosa', 22, '2024-04-27', 'Primer Proceso', 2),
(308, 'Evaluación a Ana Lorena Purisaca', 22, '2024-04-27', 'Primer Proceso', 2),
(309, 'Evaluación a OCROSPOMA UGAZ FRANK ANTHONY', 12, '2024-04-28', 'Primer Proceso', 1),
(310, 'Evaluación a FERNANDEZ ALVA EDU', 12, '2024-04-28', 'Primer Proceso', 1),
(311, 'Evaluación a SAMAME NIZAMA JOSE ALEXANDER', 12, '2024-04-28', 'Primer Proceso', 1),
(312, 'Evaluación a OCROSPOMA UGAZ FRANK ANTHONY', 12, '2024-04-29', 'Evaluación anual 2024', 4),
(313, 'Evaluación a FERNANDEZ ALVA EDU', 12, '2024-04-29', 'Evaluación anual 2024', 4),
(314, 'Evaluación a SAMAME NIZAMA JOSE ALEXANDER', 12, '2024-04-29', 'Evaluación anual 2024', 4),
(407, 'Evaluación a Jose Luis Garcia ', 22, '2024-05-03', 'Segundo Proceso', 1),
(408, 'Evaluación a Pedro Jose Suarez', 22, '2024-05-03', 'Segundo Proceso', 4),
(410, 'Evaluación a Frank Ocrospoma', 23, '2024-05-03', 'Primer Proceso', 3),
(411, 'Evaluación a Jose Luis Garcia ', 22, '2024-05-03', 'Proceso Feedback', 1),
(412, 'Evaluación a OCROSPOMA UGAZ FRANK ANTHONY', 12, '2024-05-03', 'Proceso Feedback 2024', 5),
(413, 'Evaluación a Juan Manuel Perez', 22, '2024-05-03', 'Proceso fin de año', 6),
(414, 'Evaluación a Pedro Jose Suarez', 22, '2024-05-03', 'Proceso fin de año', 6),
(415, 'Evaluación a Jose Luis Garcia ', 23, '2024-05-04', 'Proceso Mayo', 7),
(419, 'Evaluación a Ana Lorena Purisaca', 22, '2024-05-06', 'Proceso 6 Mayo', 8),
(420, 'Evaluación a Frank Anthony Ocrospoma', 22, '2024-05-06', 'Proceso 6 Mayo', 8),
(421, 'Evaluación a Robert Luis Sosa', 22, '2024-05-06', 'Proceso 6 Mayo', 8),
(423, 'Evaluación a OCROSPOMA UGAZ FRANK ANTHONY', 12, '2024-05-06', 'PROCESO pb01', 1),
(424, 'Evaluación a Lorena Maria Sosa', 23, '2024-05-06', 'Segundo Proceso', 8),
(425, 'Evaluación a Frank Ocrospoma', 23, '2024-05-06', 'Segundo Proceso', 8),
(426, 'Evaluación a Jose Luis Garcia ', 23, '2024-05-06', 'Segundo Proceso', 8);

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

--
-- Volcado de datos para la tabla `envios`
--

INSERT INTO `envios` (`id`, `persona`, `encuesta`, `estado`, `uuid`, `rango`) VALUES
(108, 42, 309, 'F', '36c44c9c-6db3-42d3-8452-23cfb6504f49', 3.00),
(109, 43, 309, 'F', 'bd04b22d-a8fe-4423-916b-31fb39572a41', 5.00),
(110, 45, 309, 'P', '63d04efd-601e-4656-8588-bb246bb098ba', NULL),
(111, 46, 309, 'P', '16af731d-22af-41e4-a791-36a6d776310d', NULL),
(112, 42, 312, 'F', 'b0cc0557-6bbb-48ba-a67c-5f26c77f7cc3', 2.30),
(113, 43, 312, 'P', '704043f0-2362-46e9-ac5d-c29c415c6e6c', NULL),
(114, 45, 312, 'P', 'dcd1e4e7-714b-40e6-9189-1a68b0d232a5', NULL),
(115, 46, 312, 'P', '8c305871-7b93-4e3e-b6f9-b428a7fe287e', NULL),
(116, 237, 307, 'P', '665b9b80-88f5-4c5d-96dd-6a8744b8f586', NULL),
(117, 234, 307, 'P', '5e024a2b-a4e4-4752-8bf6-de68945b6242', NULL),
(118, 235, 307, 'P', 'b22ad89f-551f-4ac7-a1a6-d545e9362fb0', NULL),
(119, 236, 307, 'P', '40297e71-2bb3-46e7-a39a-770399311a06', NULL),
(120, 238, 307, 'F', 'f17a2070-930e-4398-9361-88af629823ed', 2.93),
(121, 233, 307, 'P', 'b6c336b3-ab8c-44d6-ba22-ef9ff72e5802', NULL),
(122, 233, 408, 'P', '50d7f221-915c-4aa0-b2bd-38ce55567928', NULL),
(123, 234, 408, 'P', 'd086dd4f-1374-408e-aeea-ad005b7999d8', NULL),
(124, 235, 408, 'P', '8369e8b2-15f5-49a3-9614-2db63daf23f5', NULL),
(125, 237, 408, 'P', '88fec802-c698-4bfb-8d8a-60a43cba5dba', NULL),
(126, 233, 407, 'P', 'ddfb97f2-de0d-4c21-8ce4-13042e70d52d', NULL),
(127, 234, 407, 'F', '3fd51ffc-0bca-48a9-94c2-a195e1587ee8', 4.00),
(128, 235, 407, 'F', '7ead0a69-91af-4e2a-8bf3-18ea25c0086c', 3.00),
(133, 239, 410, 'P', '9aea3c84-b68d-4ec7-8a5e-6becdef6dbd1', NULL),
(134, 240, 410, 'P', '196149e9-735e-447f-9cb7-1a90527a2d85', NULL),
(135, 241, 410, 'P', '87c16235-05f5-46d6-bd4b-979f0d878d18', NULL),
(136, 233, 411, 'P', '514a541b-d188-4489-a58b-2887ae252e25', NULL),
(137, 234, 411, 'P', '6a97f03f-9630-487f-b0ed-a3e5b09cb132', NULL),
(138, 42, 412, 'F', '425f68dd-412b-4945-93a1-a4ef6c09598c', 3.31),
(139, 43, 412, 'P', '926c2515-5d79-4c98-96f0-0b0d82c3943b', NULL),
(140, 236, 413, 'P', '524a4d18-f66f-435d-8fb3-4d2549b884f6', NULL),
(141, 233, 413, 'P', 'd9886835-ac17-46df-93b9-8f1265adbb87', NULL),
(142, 234, 413, 'P', '059b1e2c-e7fc-4faa-ac76-db2ecff9ebad', NULL),
(143, 235, 413, 'F', '8fe14fe2-f143-4003-ac8e-133ad13b0fc7', 3.25),
(144, 239, 415, 'F', '20a5a56b-8f23-40af-8542-abd159672cff', 3.50),
(145, 240, 415, 'P', '28593db4-cc86-48ea-a4fd-646113520be8', NULL),
(146, 43, 310, 'P', '7c7aae25-8a3c-4be4-a6f5-543d67197eea', NULL),
(147, 42, 310, 'P', 'd064b5aa-5a81-4b0a-a4c8-f8c99dcf1c13', NULL),
(148, 45, 310, 'P', '373ca994-ce4f-4978-8baf-eeae5a598643', NULL),
(149, 47, 310, 'P', 'eb3b09c9-ce1a-45f8-8433-5e6408df708c', NULL),
(154, 43, 310, 'P', '4fdddbdb-33e7-4894-b802-7fe7263f4b45', NULL),
(155, 42, 310, 'P', '784a4462-fd96-41ec-afc8-06f04b0ea281', NULL),
(156, 45, 310, 'P', '971950d1-eef2-4c63-b918-c201af692733', NULL),
(157, 47, 310, 'P', 'fdf4fedd-3aa0-44fd-9aeb-b15bd3212c4e', NULL),
(158, 45, 311, 'P', '16bbc7ed-cc7a-41af-8c72-9dcfcb9d1649', NULL),
(159, 42, 311, 'P', '7465ec5e-e925-4454-aa81-a90320e21463', NULL),
(160, 43, 311, 'P', 'bddecf08-fafa-416b-b583-68f08b40ca08', NULL),
(161, 46, 311, 'P', 'd39d04a0-eccd-418c-9bfb-685d96973437', NULL),
(162, 235, 305, 'P', '084d9ca7-dfaf-4675-997e-e1692edc5607', NULL),
(163, 233, 305, 'P', '38652fbe-7f29-44ed-8a9a-e7b79224006b', NULL),
(164, 234, 305, 'P', 'defab587-8bc0-4329-9155-75a8301c95b4', NULL),
(165, 238, 305, 'P', '12e28266-dbf9-4f5a-889f-ba3178e427ac', NULL),
(166, 237, 305, 'P', '4b80e7e2-4bab-4ede-8328-98a0fe529fdb', NULL),
(167, 236, 305, 'P', '1eff4039-4f57-4935-bd19-816c10c41314', NULL),
(168, 236, 306, 'P', '279e79ad-5035-420d-bae9-9816893b76d8', NULL),
(169, 233, 306, 'P', '7351e414-fcd1-4003-b21c-c274cc1d4978', NULL),
(170, 237, 306, 'P', '2c22bafb-ec08-40d5-9621-2b461e101979', NULL),
(171, 238, 306, 'P', 'de81b6df-57ff-457f-90a5-dc89e3e6e5d3', NULL),
(172, 234, 306, 'P', '035baf1f-8de1-477b-8787-61d4613d1ec0', NULL),
(173, 235, 306, 'P', '1286a1ea-6db1-4b5b-981c-a48c2fdc6efa', NULL),
(180, 238, 308, 'P', '0bd12c04-8b13-41c9-bf08-935955f328f2', NULL),
(181, 233, 308, 'P', '54ea952f-f311-4524-b962-9d2a09b05f73', NULL),
(182, 236, 308, 'P', 'c6b60cb7-d1c4-44bd-9e33-d54d1364a6b0', NULL),
(183, 234, 308, 'P', '220b0146-501b-4ab1-b901-d4ad2ac3ccc5', NULL),
(184, 235, 308, 'P', '7fced30d-bb05-43a3-befa-7d032f1db951', NULL),
(185, 237, 308, 'P', '7b7371f7-94ba-4322-b778-88477aa4d3c1', NULL),
(186, 235, 414, 'P', 'ce1d7caa-9a7e-42f0-b0ed-994b16cbb7aa', NULL),
(187, 233, 414, 'P', 'c6741df7-d3bc-4587-993e-f8ab33c78066', NULL),
(188, 234, 414, 'P', 'b1a58882-ca0b-4a8a-b45f-310782df6e3c', NULL),
(189, 236, 414, 'P', '1f6744ea-59d1-4a01-91b8-b1d16439ff04', NULL),
(192, 43, 313, 'P', '5be3efe6-2180-4da9-9734-3f142340538f', NULL),
(193, 47, 313, 'P', '1fb96bcf-424c-4c77-abe6-0727886c7ad5', NULL),
(194, 45, 313, 'P', 'c5af79e5-13f9-40fe-8705-e33878a5812b', NULL),
(195, 42, 313, 'P', '2c55290d-f595-4c2c-be03-bbe4f6baaa4f', NULL),
(196, 43, 314, 'P', '49151ca5-a626-47a4-b381-515f9e177e7a', NULL),
(197, 46, 314, 'P', 'c9b58858-6891-42a9-8bb6-42b2d470dcd0', NULL),
(198, 42, 314, 'P', '8e6b7346-10a3-49b0-a218-887c616cb08b', NULL),
(199, 45, 314, 'P', 'af0a56c3-006f-47f7-803f-843f6e0a9185', NULL),
(200, 233, 419, 'P', 'a14b7cc0-f346-43bc-83d8-ffb598c7a956', NULL),
(201, 234, 419, 'P', '15bc46d6-c7b1-4072-be24-9b4c101ddc84', NULL),
(202, 235, 419, 'P', 'ac932c83-5b3a-40b3-9607-5885c3136902', NULL),
(203, 238, 419, 'P', '10cf341e-4cb4-4b83-8b9e-10efeba02c0a', NULL),
(204, 233, 420, 'P', '55a603f4-9b55-4dd4-8a8b-59f122743052', NULL),
(205, 234, 420, 'P', '655e8bb1-bd64-4c72-8680-7bb94c669099', NULL),
(206, 235, 420, 'P', 'd7f65477-b376-4d61-a58c-74221b69cbc2', NULL),
(207, 236, 420, 'P', 'a92c20f4-fd9c-4571-bd61-ac0c254a5aa0', NULL),
(208, 233, 421, 'P', '6c18f407-5f1f-4d65-9379-f3a4365d37ce', NULL),
(209, 234, 421, 'P', '5fee93ad-9a2f-4bff-8a04-e613bca5259c', NULL),
(210, 235, 421, 'P', '21e2ea9a-6d77-4f2b-a295-e4692e5b48f2', NULL),
(211, 237, 421, 'F', 'c0baa2de-3528-4465-82f9-f67fb66f09ac', 3.63),
(212, 42, 423, 'P', '670e18f8-2138-4992-a543-9ab689c4162d', NULL),
(213, 43, 423, 'P', 'a3ed17c2-3e01-4f8e-88c9-b485ffd4f6e4', NULL),
(214, 243, 424, 'F', 'c03789b9-adbc-4811-9e0f-ff3561463167', 3.25),
(215, 239, 424, 'P', 'e9758ef4-b973-4353-9912-15473f221732', NULL),
(216, 240, 424, 'P', '9134b8c9-ba15-48b4-b978-4ac4079df8f6', NULL),
(217, 241, 424, 'P', '15338f69-1d45-4e61-9814-ab2714409bd0', NULL),
(218, 239, 425, 'P', '7ba4f672-3f1c-4528-96dc-13f10850ba89', NULL),
(219, 240, 425, 'P', '24a463ea-cdd1-4c8c-92d5-4f2b6cd786ca', NULL),
(220, 241, 425, 'P', 'ef4956b8-a881-4fd1-8c90-714fa00f102a', NULL),
(221, 242, 425, 'P', '3e42f3e6-a982-4eab-9f4c-afef629685f6', NULL),
(227, 239, 426, 'F', '11752ea2-9ab2-4a88-bac1-78bf2612370a', 2.38),
(228, 240, 426, 'F', '3e0cee2a-ddbd-4c41-a90a-a791c9707785', 2.50),
(229, 241, 426, 'F', '9bf936ba-d96f-40bd-9311-ea2cc1793dce', 3.50),
(230, 242, 426, 'F', '1ea0dd28-944d-402f-a2ca-61a9cd1afeda', 3.31);

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
(482, 243, 241, 424, 3, 23);

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
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `formularios`
--

INSERT INTO `formularios` (`id`, `detalle_id`, `nombre`) VALUES
(1, 11, 'Formulario 01'),
(1, 12, 'Formulario 01'),
(1, 13, 'Formulario 01'),
(1, 14, 'Formulario 01'),
(1, 15, 'Formulario 01'),
(1, 154, 'Formulario 01'),
(2, 11, 'Formulario 02'),
(2, 12, 'Formulario 02'),
(2, 13, 'Formulario 02'),
(2, 14, 'Formulario 02'),
(2, 15, 'Formulario 02'),
(2, 16, 'Formulario 02'),
(2, 17, 'Formulario 02'),
(2, 18, 'Formulario 02'),
(2, 19, 'Formulario 02'),
(2, 20, 'Formulario 02'),
(2, 21, 'Formulario 02'),
(2, 22, 'Formulario 02'),
(2, 23, 'Formulario 02'),
(2, 24, 'Formulario 02'),
(2, 25, 'Formulario 02'),
(2, 26, 'Formulario 02'),
(2, 27, 'Formulario 02'),
(2, 28, 'Formulario 02'),
(2, 29, 'Formulario 02'),
(2, 30, 'Formulario 02'),
(2, 31, 'Formulario 02'),
(2, 32, 'Formulario 02'),
(2, 33, 'Formulario 02'),
(2, 34, 'Formulario 02'),
(2, 35, 'Formulario 02'),
(2, 36, 'Formulario 02'),
(2, 37, 'Formulario 02'),
(2, 38, 'Formulario 02'),
(2, 39, 'Formulario 02'),
(2, 40, 'Formulario 02'),
(2, 41, 'Formulario 02'),
(2, 42, 'Formulario 02'),
(2, 43, 'Formulario 02'),
(2, 44, 'Formulario 02'),
(2, 45, 'Formulario 02'),
(2, 46, 'Formulario 02'),
(2, 47, 'Formulario 02'),
(2, 48, 'Formulario 02'),
(2, 49, 'Formulario 02'),
(2, 50, 'Formulario 02'),
(2, 51, 'Formulario 02'),
(2, 52, 'Formulario 02'),
(2, 53, 'Formulario 02'),
(2, 54, 'Formulario 02'),
(2, 55, 'Formulario 02'),
(2, 56, 'Formulario 02'),
(2, 57, 'Formulario 02'),
(2, 58, 'Formulario 02'),
(2, 59, 'Formulario 02'),
(2, 60, 'Formulario 02'),
(2, 61, 'Formulario 02'),
(2, 62, 'Formulario 02'),
(2, 63, 'Formulario 02'),
(2, 64, 'Formulario 02'),
(2, 65, 'Formulario 02'),
(2, 66, 'Formulario 02'),
(2, 67, 'Formulario 02'),
(2, 68, 'Formulario 02'),
(2, 69, 'Formulario 02'),
(2, 70, 'Formulario 02'),
(2, 71, 'Formulario 02'),
(2, 72, 'Formulario 02'),
(2, 73, 'Formulario 02'),
(2, 74, 'Formulario 02'),
(2, 75, 'Formulario 02'),
(2, 76, 'Formulario 02'),
(2, 77, 'Formulario 02'),
(2, 78, 'Formulario 02'),
(2, 79, 'Formulario 02'),
(2, 80, 'Formulario 02'),
(2, 81, 'Formulario 02'),
(2, 82, 'Formulario 02'),
(2, 83, 'Formulario 02'),
(2, 84, 'Formulario 02'),
(2, 85, 'Formulario 02'),
(2, 91, 'Formulario 02'),
(2, 92, 'Formulario 02'),
(2, 93, 'Formulario 02'),
(2, 101, 'Formulario 02'),
(2, 102, 'Formulario 02'),
(2, 103, 'Formulario 02'),
(2, 109, 'Formulario 02'),
(2, 110, 'Formulario 02'),
(2, 111, 'Formulario 02'),
(2, 112, 'Formulario 02'),
(2, 113, 'Formulario 02'),
(2, 114, 'Formulario 02'),
(2, 115, 'Formulario 02'),
(2, 116, 'Formulario 02'),
(2, 117, 'Formulario 02'),
(2, 118, 'Formulario 02'),
(2, 119, 'Formulario 02'),
(2, 120, 'Formulario 02'),
(2, 121, 'Formulario 02'),
(2, 122, 'Formulario 02'),
(2, 123, 'Formulario 02'),
(2, 124, 'Formulario 02'),
(2, 125, 'Formulario 02'),
(2, 126, 'Formulario 02'),
(2, 127, 'Formulario 02'),
(2, 128, 'Formulario 02'),
(2, 129, 'Formulario 02'),
(2, 130, 'Formulario 02'),
(2, 131, 'Formulario 02'),
(2, 132, 'Formulario 02'),
(2, 133, 'Formulario 02'),
(2, 134, 'Formulario 02'),
(2, 135, 'Formulario 02'),
(2, 136, 'Formulario 02'),
(2, 137, 'Formulario 02'),
(2, 138, 'Formulario 02'),
(2, 139, 'Formulario 02'),
(2, 140, 'Formulario 02'),
(2, 141, 'Formulario 02'),
(2, 142, 'Formulario 02'),
(2, 143, 'Formulario 02'),
(2, 144, 'Formulario 02'),
(2, 145, 'Formulario 02'),
(2, 146, 'Formulario 02'),
(2, 147, 'Formulario 02'),
(2, 148, 'Formulario 02'),
(2, 149, 'Formulario 02'),
(2, 150, 'Formulario 02'),
(2, 151, 'Formulario 02'),
(2, 152, 'Formulario 02'),
(2, 153, 'Formulario 02'),
(2, 154, 'Formulario 02'),
(2, 155, 'Formulario 02'),
(2, 156, 'Formulario 02'),
(2, 157, 'Formulario 02'),
(2, 158, 'Formulario 02'),
(2, 159, 'Formulario 02'),
(2, 160, 'Formulario 02'),
(2, 161, 'Formulario 02'),
(2, 162, 'Formulario 02'),
(2, 163, 'Formulario 02'),
(2, 164, 'Formulario 02'),
(2, 165, 'Formulario 02'),
(2, 166, 'Formulario 02'),
(2, 167, 'Formulario 02'),
(2, 168, 'Formulario 02'),
(2, 170, 'Formulario 02'),
(2, 171, 'Formulario 02'),
(2, 172, 'Formulario 02'),
(3, 11, 'Formulario 03'),
(3, 13, 'Formulario 03'),
(3, 16, 'Formulario 03'),
(3, 18, 'Formulario 03'),
(3, 21, 'Formulario 03'),
(3, 23, 'Formulario 03'),
(3, 26, 'Formulario 03'),
(3, 28, 'Formulario 03'),
(3, 31, 'Formulario 03'),
(3, 33, 'Formulario 03'),
(3, 154, 'Formulario 03'),
(3, 155, 'Formulario 03'),
(3, 156, 'Formulario 03'),
(3, 157, 'Formulario 03'),
(3, 158, 'Formulario 03'),
(4, 11, 'Formulario GarzaSoft'),
(4, 12, 'Formulario GarzaSoft'),
(4, 13, 'Formulario GarzaSoft'),
(4, 14, 'Formulario GarzaSoft'),
(4, 15, 'Formulario GarzaSoft'),
(4, 16, 'Formulario GarzaSoft'),
(4, 17, 'Formulario GarzaSoft'),
(4, 18, 'Formulario GarzaSoft'),
(4, 19, 'Formulario GarzaSoft'),
(4, 20, 'Formulario GarzaSoft'),
(4, 21, 'Formulario GarzaSoft'),
(4, 22, 'Formulario GarzaSoft'),
(4, 23, 'Formulario GarzaSoft'),
(4, 24, 'Formulario GarzaSoft'),
(4, 25, 'Formulario GarzaSoft'),
(4, 26, 'Formulario GarzaSoft'),
(4, 27, 'Formulario GarzaSoft'),
(4, 28, 'Formulario GarzaSoft'),
(4, 29, 'Formulario GarzaSoft'),
(4, 30, 'Formulario GarzaSoft'),
(4, 31, 'Formulario GarzaSoft'),
(4, 32, 'Formulario GarzaSoft'),
(4, 33, 'Formulario GarzaSoft'),
(4, 34, 'Formulario GarzaSoft'),
(4, 35, 'Formulario GarzaSoft'),
(4, 36, 'Formulario GarzaSoft'),
(4, 37, 'Formulario GarzaSoft'),
(4, 38, 'Formulario GarzaSoft'),
(4, 39, 'Formulario GarzaSoft'),
(4, 40, 'Formulario GarzaSoft'),
(4, 41, 'Formulario GarzaSoft'),
(4, 42, 'Formulario GarzaSoft'),
(4, 43, 'Formulario GarzaSoft'),
(4, 44, 'Formulario GarzaSoft'),
(4, 45, 'Formulario GarzaSoft'),
(4, 46, 'Formulario GarzaSoft'),
(4, 47, 'Formulario GarzaSoft'),
(4, 48, 'Formulario GarzaSoft'),
(4, 49, 'Formulario GarzaSoft'),
(4, 50, 'Formulario GarzaSoft'),
(4, 51, 'Formulario GarzaSoft'),
(4, 52, 'Formulario GarzaSoft'),
(4, 53, 'Formulario GarzaSoft'),
(4, 54, 'Formulario GarzaSoft'),
(4, 55, 'Formulario GarzaSoft'),
(4, 56, 'Formulario GarzaSoft'),
(4, 57, 'Formulario GarzaSoft'),
(4, 58, 'Formulario GarzaSoft'),
(4, 59, 'Formulario GarzaSoft'),
(4, 60, 'Formulario GarzaSoft'),
(4, 154, 'Formulario GarzaSoft'),
(4, 155, 'Formulario GarzaSoft'),
(4, 156, 'Formulario GarzaSoft'),
(4, 157, 'Formulario GarzaSoft'),
(4, 158, 'Formulario GarzaSoft'),
(4, 159, 'Formulario GarzaSoft'),
(4, 160, 'Formulario GarzaSoft'),
(4, 161, 'Formulario GarzaSoft'),
(4, 162, 'Formulario GarzaSoft'),
(4, 163, 'Formulario GarzaSoft'),
(5, 11, 'Formulario 05'),
(5, 12, 'Formulario 05'),
(5, 13, 'Formulario 05'),
(5, 14, 'Formulario 05'),
(5, 15, 'Formulario 05'),
(5, 16, 'Formulario 05'),
(5, 17, 'Formulario 05'),
(5, 18, 'Formulario 05'),
(5, 19, 'Formulario 05'),
(5, 20, 'Formulario 05'),
(5, 21, 'Formulario 05'),
(5, 22, 'Formulario 05'),
(5, 23, 'Formulario 05'),
(5, 24, 'Formulario 05'),
(5, 25, 'Formulario 05'),
(5, 26, 'Formulario 05'),
(5, 27, 'Formulario 05'),
(5, 28, 'Formulario 05'),
(5, 29, 'Formulario 05'),
(5, 30, 'Formulario 05'),
(5, 31, 'Formulario 05'),
(5, 32, 'Formulario 05'),
(5, 33, 'Formulario 05'),
(5, 34, 'Formulario 05'),
(5, 35, 'Formulario 05'),
(5, 36, 'Formulario 05'),
(5, 37, 'Formulario 05'),
(5, 38, 'Formulario 05'),
(5, 39, 'Formulario 05'),
(5, 40, 'Formulario 05'),
(5, 41, 'Formulario 05'),
(5, 42, 'Formulario 05'),
(5, 43, 'Formulario 05'),
(5, 44, 'Formulario 05'),
(5, 45, 'Formulario 05'),
(5, 46, 'Formulario 05'),
(5, 47, 'Formulario 05'),
(5, 48, 'Formulario 05'),
(5, 49, 'Formulario 05'),
(5, 50, 'Formulario 05'),
(5, 51, 'Formulario 05'),
(5, 52, 'Formulario 05'),
(5, 53, 'Formulario 05'),
(5, 54, 'Formulario 05'),
(5, 55, 'Formulario 05'),
(5, 56, 'Formulario 05'),
(5, 57, 'Formulario 05'),
(5, 58, 'Formulario 05'),
(5, 59, 'Formulario 05'),
(5, 60, 'Formulario 05'),
(5, 61, 'Formulario 05'),
(5, 62, 'Formulario 05'),
(5, 63, 'Formulario 05'),
(5, 64, 'Formulario 05'),
(5, 65, 'Formulario 05'),
(5, 66, 'Formulario 05'),
(5, 67, 'Formulario 05'),
(5, 68, 'Formulario 05'),
(5, 69, 'Formulario 05'),
(5, 70, 'Formulario 05'),
(5, 71, 'Formulario 05'),
(5, 72, 'Formulario 05'),
(5, 73, 'Formulario 05'),
(5, 74, 'Formulario 05'),
(5, 75, 'Formulario 05'),
(5, 76, 'Formulario 05'),
(5, 77, 'Formulario 05'),
(5, 78, 'Formulario 05'),
(5, 79, 'Formulario 05'),
(5, 80, 'Formulario 05'),
(5, 81, 'Formulario 05'),
(5, 82, 'Formulario 05'),
(5, 83, 'Formulario 05'),
(5, 84, 'Formulario 05'),
(5, 85, 'Formulario 05'),
(5, 104, 'Formulario 05'),
(5, 105, 'Formulario 05'),
(5, 106, 'Formulario 05'),
(5, 107, 'Formulario 05'),
(5, 108, 'Formulario 05'),
(5, 154, 'Formulario 05'),
(5, 155, 'Formulario 05'),
(5, 156, 'Formulario 05'),
(5, 157, 'Formulario 05'),
(5, 158, 'Formulario 05'),
(5, 159, 'Formulario 05'),
(5, 160, 'Formulario 05'),
(5, 161, 'Formulario 05'),
(5, 162, 'Formulario 05'),
(5, 163, 'Formulario 05'),
(5, 164, 'Formulario 05'),
(5, 165, 'Formulario 05'),
(5, 166, 'Formulario 05'),
(5, 167, 'Formulario 05'),
(5, 168, 'Formulario 05'),
(5, 169, 'Formulario 05'),
(6, 11, 'Formulario fin de año Tarma'),
(6, 12, 'Formulario fin de año Tarma'),
(6, 13, 'Formulario fin de año Tarma'),
(6, 14, 'Formulario fin de año Tarma'),
(6, 15, 'Formulario fin de año Tarma'),
(6, 16, 'Formulario fin de año Tarma'),
(6, 17, 'Formulario fin de año Tarma'),
(6, 18, 'Formulario fin de año Tarma'),
(6, 19, 'Formulario fin de año Tarma'),
(6, 20, 'Formulario fin de año Tarma'),
(6, 21, 'Formulario fin de año Tarma'),
(6, 22, 'Formulario fin de año Tarma'),
(6, 23, 'Formulario fin de año Tarma'),
(6, 24, 'Formulario fin de año Tarma'),
(6, 25, 'Formulario fin de año Tarma'),
(6, 26, 'Formulario fin de año Tarma'),
(6, 27, 'Formulario fin de año Tarma'),
(6, 28, 'Formulario fin de año Tarma'),
(6, 29, 'Formulario fin de año Tarma'),
(6, 30, 'Formulario fin de año Tarma'),
(6, 31, 'Formulario fin de año Tarma'),
(6, 32, 'Formulario fin de año Tarma'),
(6, 33, 'Formulario fin de año Tarma'),
(6, 34, 'Formulario fin de año Tarma'),
(6, 35, 'Formulario fin de año Tarma'),
(6, 36, 'Formulario fin de año Tarma'),
(6, 37, 'Formulario fin de año Tarma'),
(6, 38, 'Formulario fin de año Tarma'),
(6, 39, 'Formulario fin de año Tarma'),
(6, 40, 'Formulario fin de año Tarma'),
(6, 41, 'Formulario fin de año Tarma'),
(6, 42, 'Formulario fin de año Tarma'),
(6, 43, 'Formulario fin de año Tarma'),
(6, 44, 'Formulario fin de año Tarma'),
(6, 45, 'Formulario fin de año Tarma'),
(6, 46, 'Formulario fin de año Tarma'),
(6, 47, 'Formulario fin de año Tarma'),
(6, 48, 'Formulario fin de año Tarma'),
(6, 49, 'Formulario fin de año Tarma'),
(6, 50, 'Formulario fin de año Tarma'),
(6, 51, 'Formulario fin de año Tarma'),
(6, 52, 'Formulario fin de año Tarma'),
(6, 53, 'Formulario fin de año Tarma'),
(6, 54, 'Formulario fin de año Tarma'),
(6, 55, 'Formulario fin de año Tarma'),
(6, 56, 'Formulario fin de año Tarma'),
(6, 57, 'Formulario fin de año Tarma'),
(6, 58, 'Formulario fin de año Tarma'),
(6, 59, 'Formulario fin de año Tarma'),
(6, 60, 'Formulario fin de año Tarma'),
(6, 61, 'Formulario fin de año Tarma'),
(6, 62, 'Formulario fin de año Tarma'),
(6, 63, 'Formulario fin de año Tarma'),
(6, 64, 'Formulario fin de año Tarma'),
(6, 65, 'Formulario fin de año Tarma'),
(6, 66, 'Formulario fin de año Tarma'),
(6, 67, 'Formulario fin de año Tarma'),
(6, 68, 'Formulario fin de año Tarma'),
(6, 69, 'Formulario fin de año Tarma'),
(6, 70, 'Formulario fin de año Tarma'),
(6, 71, 'Formulario fin de año Tarma'),
(6, 72, 'Formulario fin de año Tarma'),
(6, 73, 'Formulario fin de año Tarma'),
(6, 74, 'Formulario fin de año Tarma'),
(6, 75, 'Formulario fin de año Tarma'),
(6, 76, 'Formulario fin de año Tarma'),
(6, 77, 'Formulario fin de año Tarma'),
(6, 78, 'Formulario fin de año Tarma'),
(6, 79, 'Formulario fin de año Tarma'),
(6, 80, 'Formulario fin de año Tarma'),
(6, 81, 'Formulario fin de año Tarma'),
(6, 82, 'Formulario fin de año Tarma'),
(6, 83, 'Formulario fin de año Tarma'),
(6, 84, 'Formulario fin de año Tarma'),
(6, 85, 'Formulario fin de año Tarma'),
(6, 104, 'Formulario fin de año Tarma'),
(6, 105, 'Formulario fin de año Tarma'),
(6, 106, 'Formulario fin de año Tarma'),
(6, 107, 'Formulario fin de año Tarma'),
(6, 108, 'Formulario fin de año Tarma'),
(6, 154, 'Formulario fin de año Tarma'),
(6, 155, 'Formulario fin de año Tarma'),
(6, 156, 'Formulario fin de año Tarma'),
(6, 157, 'Formulario fin de año Tarma'),
(6, 158, 'Formulario fin de año Tarma'),
(6, 159, 'Formulario fin de año Tarma'),
(6, 160, 'Formulario fin de año Tarma'),
(6, 161, 'Formulario fin de año Tarma'),
(6, 162, 'Formulario fin de año Tarma'),
(6, 163, 'Formulario fin de año Tarma'),
(6, 164, 'Formulario fin de año Tarma'),
(6, 165, 'Formulario fin de año Tarma'),
(6, 166, 'Formulario fin de año Tarma'),
(6, 167, 'Formulario fin de año Tarma'),
(6, 168, 'Formulario fin de año Tarma'),
(6, 169, 'Formulario fin de año Tarma'),
(7, 11, 'Formulario abiertas'),
(7, 12, 'Formulario abiertas'),
(7, 13, 'Formulario abiertas'),
(7, 14, 'Formulario abiertas'),
(7, 15, 'Formulario abiertas'),
(7, 16, 'Formulario abiertas'),
(7, 17, 'Formulario abiertas'),
(7, 18, 'Formulario abiertas'),
(7, 19, 'Formulario abiertas'),
(7, 20, 'Formulario abiertas'),
(7, 92, 'Formulario abiertas'),
(7, 154, 'Formulario abiertas'),
(7, 155, 'Formulario abiertas'),
(8, 11, 'Formulario prueba'),
(8, 12, 'Formulario prueba'),
(8, 13, 'Formulario prueba'),
(8, 14, 'Formulario prueba'),
(8, 15, 'Formulario prueba'),
(8, 16, 'Formulario prueba'),
(8, 17, 'Formulario prueba'),
(8, 18, 'Formulario prueba'),
(8, 19, 'Formulario prueba'),
(8, 20, 'Formulario prueba'),
(8, 21, 'Formulario prueba'),
(8, 22, 'Formulario prueba'),
(8, 23, 'Formulario prueba'),
(8, 24, 'Formulario prueba'),
(8, 25, 'Formulario prueba'),
(8, 26, 'Formulario prueba'),
(8, 27, 'Formulario prueba'),
(8, 28, 'Formulario prueba'),
(8, 29, 'Formulario prueba'),
(8, 30, 'Formulario prueba'),
(8, 31, 'Formulario prueba'),
(8, 32, 'Formulario prueba'),
(8, 33, 'Formulario prueba'),
(8, 34, 'Formulario prueba'),
(8, 35, 'Formulario prueba'),
(8, 36, 'Formulario prueba'),
(8, 37, 'Formulario prueba'),
(8, 38, 'Formulario prueba'),
(8, 39, 'Formulario prueba'),
(8, 40, 'Formulario prueba'),
(8, 41, 'Formulario prueba'),
(8, 42, 'Formulario prueba'),
(8, 43, 'Formulario prueba'),
(8, 44, 'Formulario prueba'),
(8, 45, 'Formulario prueba'),
(8, 46, 'Formulario prueba'),
(8, 47, 'Formulario prueba'),
(8, 48, 'Formulario prueba'),
(8, 49, 'Formulario prueba'),
(8, 50, 'Formulario prueba'),
(8, 51, 'Formulario prueba'),
(8, 52, 'Formulario prueba'),
(8, 53, 'Formulario prueba'),
(8, 54, 'Formulario prueba'),
(8, 55, 'Formulario prueba'),
(8, 56, 'Formulario prueba'),
(8, 57, 'Formulario prueba'),
(8, 58, 'Formulario prueba'),
(8, 59, 'Formulario prueba'),
(8, 60, 'Formulario prueba'),
(8, 61, 'Formulario prueba'),
(8, 62, 'Formulario prueba'),
(8, 63, 'Formulario prueba'),
(8, 64, 'Formulario prueba'),
(8, 65, 'Formulario prueba'),
(8, 66, 'Formulario prueba'),
(8, 67, 'Formulario prueba'),
(8, 68, 'Formulario prueba'),
(8, 69, 'Formulario prueba'),
(8, 70, 'Formulario prueba'),
(8, 71, 'Formulario prueba'),
(8, 72, 'Formulario prueba'),
(8, 73, 'Formulario prueba'),
(8, 74, 'Formulario prueba'),
(8, 75, 'Formulario prueba'),
(8, 76, 'Formulario prueba'),
(8, 77, 'Formulario prueba'),
(8, 78, 'Formulario prueba'),
(8, 79, 'Formulario prueba'),
(8, 80, 'Formulario prueba'),
(8, 81, 'Formulario prueba'),
(8, 82, 'Formulario prueba'),
(8, 83, 'Formulario prueba'),
(8, 84, 'Formulario prueba'),
(8, 85, 'Formulario prueba'),
(8, 91, 'Formulario prueba'),
(8, 92, 'Formulario prueba'),
(8, 93, 'Formulario prueba'),
(8, 104, 'Formulario prueba'),
(8, 105, 'Formulario prueba'),
(8, 106, 'Formulario prueba'),
(8, 107, 'Formulario prueba'),
(8, 108, 'Formulario prueba'),
(8, 154, 'Formulario prueba'),
(8, 155, 'Formulario prueba'),
(8, 156, 'Formulario prueba'),
(8, 157, 'Formulario prueba'),
(8, 158, 'Formulario prueba'),
(8, 159, 'Formulario prueba'),
(8, 160, 'Formulario prueba'),
(8, 161, 'Formulario prueba'),
(8, 162, 'Formulario prueba'),
(8, 163, 'Formulario prueba'),
(8, 164, 'Formulario prueba'),
(8, 165, 'Formulario prueba'),
(8, 166, 'Formulario prueba'),
(8, 167, 'Formulario prueba'),
(8, 168, 'Formulario prueba'),
(8, 169, 'Formulario prueba');

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
(1, 1, 1, '2024-03-20 07:56:29', '2024-03-20 07:56:29'),
(2, 4, 0, '2024-04-28 07:28:30', '2024-04-29 07:28:30'),
(3, 5, 0, '2024-04-28 14:35:04', '2024-04-28 14:35:04'),
(4, 6, 0, '2024-04-30 02:25:46', '2024-04-30 02:25:46'),
(5, 7, 0, '2024-05-02 22:24:32', '2024-05-02 22:24:32'),
(6, 8, 0, '2024-05-04 01:12:50', '2024-05-04 01:12:50'),
(7, 9, 0, '2024-05-04 07:42:38', '2024-05-04 07:42:38');

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

--
-- Volcado de datos para la tabla `personals`
--

INSERT INTO `personals` (`id`, `dni`, `nombre`, `correo`, `telefono`, `cargo`, `estado`) VALUES
(42, '77231754', 'OCROSPOMA UGAZ FRANK ANTHONY', 'frankocrospomaugaz@gmail.com', '920532729', 'Tester', '1'),
(43, '72050992', 'FERNANDEZ ALVA EDU', 'frankocrospomaugaz@gmail.com', '987546213', 'Administrador', '1'),
(45, '47071856', 'SAMAME NIZAMA JOSE ALEXANDER', 'alex_3849@hotmail.com', '956930067', 'Jefe de area', '1'),
(46, '16734323', 'AMPUERO PASCO GILBERTO MARTIN', 'martinampuero@hotmail.com', '958746123', 'Gerente', '1'),
(47, '75010274', 'GUZMAN MORI CARLOS GUSTAVO', 'frankocrospomaugaz@gmail.com', '985472163', 'Desarrollador', '1'),
(49, '75376346', 'GIL FERNANDEZ GEANCARLOS', 'frankocrospomaugaz@gmail.com', '958741263', 'Desarrollador', '1'),
(161, NULL, 'Jose Luis Garcia ', 'jl.garcia.rivera.cix@gmail.com', NULL, NULL, '1'),
(167, NULL, 'Mercy Heredia Son', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(168, NULL, 'Jorge Quiroz Vera', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(233, NULL, 'Jhordy Sosa Pirlo', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(234, NULL, 'Frank Anthony Ocrospoma', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(235, NULL, 'Pedro Jose Suarez', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(236, NULL, 'Juan Manuel Perez', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(237, NULL, 'Robert Luis Sosa', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(238, NULL, 'Ana Lorena Purisaca', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(239, NULL, 'Leonor Piscoya Perez', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(240, NULL, 'Rene Mendez Rocas', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(241, NULL, 'Pedro Velez Nuñez', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(242, NULL, 'Marcos Ugaz Lacerna', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(243, NULL, 'Lorena Santamaria Solis', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(244, NULL, 'Veronica Lopez Gutierrez', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1'),
(245, NULL, 'UGAZ LACERNA MARIA ESTEFANIA', 'asdasd@gmail.com', NULL, NULL, '1'),
(259, '43572166', 'PEREZ ROBLES LUIS MIGUEL', 'frankocrospomaugaz@gmail.com', '958745876', 'Desarrollador', '1'),
(260, NULL, 'Manuel Barrero', 'frankocrospomaugaz@gmail.com', NULL, NULL, '1');

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

--
-- Volcado de datos para la tabla `persona_respuestas`
--

INSERT INTO `persona_respuestas` (`id`, `persona`, `detalle`, `encuesta_id`) VALUES
(1153, 42, 13, 309),
(1154, 43, 15, 309),
(1155, 42, 11, 312),
(1156, 42, 155, 312),
(1157, 42, 24, 312),
(1158, 42, 45, 312),
(1159, 42, 161, 312),
(1160, 42, 53, 312),
(1161, 42, 58, 312),
(1162, 42, 30, 312),
(1163, 42, 158, 312),
(1164, 42, 37, 312),
(1184, 238, 13, 307),
(1185, 238, 19, 307),
(1186, 238, 156, 307),
(1187, 238, 45, 307),
(1188, 238, 48, 307),
(1189, 238, 54, 307),
(1190, 238, 59, 307),
(1191, 238, 63, 307),
(1192, 238, 70, 307),
(1193, 238, 167, 307),
(1194, 238, 85, 307),
(1195, 238, 75, 307),
(1196, 238, 157, 307),
(1197, 238, 33, 307),
(1198, 238, 159, 307),
(1199, 238, 205, 307),
(1200, 238, 206, 307),
(1201, 238, 207, 307),
(1202, 235, 13, 407),
(1203, 234, 14, 407),
(1204, 234, 154, 408),
(1205, 234, 18, 408),
(1206, 234, 44, 408),
(1207, 234, 54, 408),
(1226, 42, 14, 412),
(1227, 42, 20, 412),
(1228, 42, 22, 412),
(1229, 42, 44, 412),
(1230, 42, 49, 412),
(1231, 42, 53, 412),
(1232, 42, 59, 412),
(1233, 42, 62, 412),
(1234, 42, 68, 412),
(1235, 42, 80, 412),
(1236, 42, 83, 412),
(1237, 42, 166, 412),
(1238, 42, 107, 412),
(1239, 42, 157, 412),
(1240, 42, 35, 412),
(1241, 42, 40, 412),
(1245, 235, 14, 413),
(1246, 235, 155, 413),
(1247, 235, 156, 413),
(1248, 235, 43, 413),
(1249, 235, 48, 413),
(1250, 235, 53, 413),
(1251, 235, 58, 413),
(1252, 235, 65, 413),
(1253, 235, 69, 413),
(1254, 235, 78, 413),
(1255, 235, 84, 413),
(1256, 235, 74, 413),
(1257, 235, 106, 413),
(1258, 235, 29, 413),
(1259, 235, 35, 413),
(1260, 235, 39, 413),
(1311, 239, 13, 415),
(1312, 239, 19, 415),
(1313, 239, 224, 415),
(1342, 237, 13, 421),
(1343, 237, 19, 421),
(1344, 237, 25, 421),
(1345, 237, 160, 421),
(1346, 237, 49, 421),
(1347, 237, 55, 421),
(1348, 237, 60, 421),
(1349, 237, 63, 421),
(1350, 237, 69, 421),
(1351, 237, 75, 421),
(1352, 237, 80, 421),
(1353, 237, 168, 421),
(1354, 237, 27, 421),
(1355, 237, 33, 421),
(1356, 237, 40, 421),
(1357, 237, 108, 421),
(1358, 237, 231, 421),
(1359, 237, 232, 421),
(1360, 237, 233, 421),
(1371, 241, 13, 426),
(1372, 241, 19, 426),
(1373, 241, 24, 426),
(1374, 241, 43, 426),
(1375, 241, 48, 426),
(1376, 241, 54, 426),
(1377, 241, 60, 426),
(1378, 241, 65, 426),
(1379, 241, 165, 426),
(1380, 241, 73, 426),
(1381, 241, 79, 426),
(1382, 241, 85, 426),
(1383, 241, 107, 426),
(1384, 241, 30, 426),
(1385, 241, 34, 426),
(1386, 241, 159, 426),
(1387, 241, 234, 426),
(1388, 241, 235, 426),
(1389, 241, 236, 426),
(1409, 242, 13, 426),
(1410, 242, 18, 426),
(1411, 242, 24, 426),
(1412, 242, 44, 426),
(1413, 242, 161, 426),
(1414, 242, 51, 426),
(1415, 242, 58, 426),
(1416, 242, 65, 426),
(1417, 242, 70, 426),
(1418, 242, 73, 426),
(1419, 242, 80, 426),
(1420, 242, 82, 426),
(1421, 242, 108, 426),
(1422, 242, 28, 426),
(1423, 242, 34, 426),
(1424, 242, 38, 426),
(1425, 242, 240, 426),
(1426, 242, 241, 426),
(1427, 242, 242, 426),
(1440, 240, 13, 426),
(1441, 240, 19, 426),
(1442, 240, 156, 426),
(1443, 240, 43, 426),
(1444, 240, 50, 426),
(1445, 240, 53, 426),
(1446, 240, 163, 426),
(1447, 240, 62, 426),
(1448, 240, 165, 426),
(1449, 240, 73, 426),
(1450, 240, 80, 426),
(1451, 240, 84, 426),
(1452, 240, 31, 426),
(1453, 240, 38, 426),
(1454, 240, 106, 426),
(1455, 240, 26, 426),
(1456, 240, 243, 426),
(1457, 240, 244, 426),
(1458, 240, 245, 426),
(1459, 239, 13, 426),
(1460, 239, 155, 426),
(1461, 239, 24, 426),
(1462, 239, 43, 426),
(1463, 239, 49, 426),
(1464, 239, 51, 426),
(1465, 239, 62, 426),
(1466, 239, 165, 426),
(1467, 239, 56, 426),
(1468, 239, 72, 426),
(1469, 239, 77, 426),
(1470, 239, 168, 426),
(1471, 239, 106, 426),
(1472, 239, 30, 426),
(1473, 239, 33, 426),
(1474, 239, 40, 426),
(1475, 239, 246, 426),
(1476, 239, 247, 426),
(1477, 239, 248, 426),
(1480, 243, 15, 424),
(1481, 243, 19, 424),
(1482, 243, 23, 424),
(1483, 243, 42, 424),
(1484, 243, 48, 424),
(1485, 243, 53, 424),
(1486, 243, 57, 424),
(1487, 243, 64, 424),
(1488, 243, 69, 424),
(1489, 243, 75, 424),
(1490, 243, 79, 424),
(1491, 243, 85, 424),
(1492, 243, 107, 424),
(1493, 243, 27, 424),
(1494, 243, 32, 424),
(1495, 243, 159, 424),
(1496, 243, 249, 424),
(1497, 243, 250, 424),
(1498, 243, 251, 424);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id` int(11) NOT NULL,
  `texto` varchar(1024) NOT NULL,
  `categoria` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id`, `texto`, `categoria`, `estado`) VALUES
(31, 'Trabaja bien con los demás y contribuye a los objetivos del equipo', 1, 1),
(32, 'Comparte conocimientos y recursos con los demás', 1, 1),
(33, 'Respeta las opiniones e ideas de los demás', 1, 1),
(34, 'Responde a las preguntas de los clientes de manera oportuna y profesional', 5, 1),
(35, 'Asume la responsabilidad de los problemas de los clientes y los resuelve', 5, 1),
(36, 'Demuestra empatía y paciencia cuando trata con clientes difíciles', 5, 1),
(37, 'Aplica un enfoque sistemático a la resolución de problemas', 2, 1),
(38, 'Piensa de forma creativa para encontrar nuevas soluciones a los problemas', 2, 1),
(39, ' Aprende de sus errores pasados y aplica esas lecciones a la resolución de problemas futuros', 2, 1),
(40, ' Se comunica de forma clara y eficaz con los demás', 3, 1),
(41, 'Proporciona retroalimentación de forma constructiva y respetuosa', 3, 1),
(42, 'Adapta su estilo de comunicación a los distintos públicos', 3, 1),
(43, 'Realiza una amplia gama de tareas y responde a los cambios de dirección y prioridades', 4, 1),
(44, 'Trabaja eficazmente en entornos de trabajo dinámicos y cambiantes', 4, 1),
(45, ' Adapta sus planes u horarios a situaciones cambiantes', 4, 1),
(49, 'Cuáles crees que son sus principales fortalezas? (2 a 3 fortalezas)', NULL, 0),
(50, 'Cuáles crees que son sus principales oportunidades de mejora? (2 a 3 oportunidades)', NULL, 0),
(51, 'Si solo pudiese enfocarse en mejorar una conducta cuál debería ser', NULL, 0),
(52, 'Realiza los trabajos con responsabilidad y puntualidad', 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `id` int(11) NOT NULL,
  `texto` text NOT NULL,
  `score` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `respuestas`
--

INSERT INTO `respuestas` (`id`, `texto`, `score`, `estado`) VALUES
(1, 'Oportunidad Crítica', 1, 1),
(2, 'Debe Mejorar', 2, 1),
(3, 'Regular', 3, 1),
(4, 'Hábil', 4, 1),
(5, 'Destaca', 5, 1),
(47, 'xdd', NULL, 0),
(48, 'barri', NULL, 0),
(49, 'xvz', NULL, 0),
(50, 'abccc', NULL, 0),
(51, 'varias', NULL, 0),
(52, 'sisisis', NULL, 0),
(53, 'wea', NULL, 0),
(54, 'xdd', NULL, 0),
(55, 'avx', NULL, 0),
(56, 'abc', NULL, 0),
(57, 'abc', NULL, 0),
(58, 'xdy', NULL, 0),
(59, 'sdca', NULL, 0),
(60, 'acds', NULL, 0),
(61, 'acda', NULL, 0),
(62, 'abvc', NULL, 0),
(63, 'xyz', NULL, 0),
(64, 'vbn', NULL, 0),
(65, 'wrevfr', NULL, 0),
(66, 'sdvs', NULL, 0),
(67, 'sdvasd', NULL, 0),
(68, 'weaa', NULL, 0),
(69, 'sisi', NULL, 0),
(70, 'aeaaaa', NULL, 0),
(71, 'df', NULL, 0),
(72, 'sdvwsd', NULL, 0),
(73, 'sadvc', NULL, 0),
(74, 'sddf', NULL, 0),
(75, 'adefa', NULL, 0),
(76, 'abc', NULL, 0),
(77, 'SFDGS', NULL, 0),
(78, 'SFVBSDVS', NULL, 0),
(79, 'SFDVBSFB', NULL, 0),
(80, 'SDGA', NULL, 0),
(81, 'SFBSF', NULL, 0),
(82, 'SDVBFSDB', NULL, 0),
(83, 'XDBFSD', NULL, 0),
(84, 'FBSFDB', NULL, 0),
(85, 'FBFDG', NULL, 0),
(86, 'SDVG', NULL, 0),
(87, 'DVSD', NULL, 0),
(88, 'DVDXS', NULL, 0),
(89, 'SXDFV', NULL, 0),
(90, 'SFDVBSV', NULL, 0),
(91, 'SSFDVBFSDZX', NULL, 0),
(92, 'XFVSDXF', NULL, 0),
(93, 'DFVSXFFV', NULL, 0),
(94, 'FSDGSRF', NULL, 0),
(96, 'No Aplica', 0, 1),
(97, 'wrffdrvs', NULL, 0),
(98, 'sdvs', NULL, 0),
(99, 'sdfvfsds', NULL, 0),
(102, 'aedas', NULL, 0),
(103, 'adcd', NULL, 0),
(104, 'adca', NULL, 0),
(105, 'ssisisi', NULL, 0),
(106, 'wefwe', NULL, 0),
(107, 'sdcsd', NULL, 0),
(108, 'adcd', NULL, 0),
(109, 'sas', NULL, 0),
(110, 'qwdqw', NULL, 0),
(111, 'ewsdcas', NULL, 0),
(112, 'sdfvsf', NULL, 0),
(113, 'acas', NULL, 0),
(114, 'abc', NULL, 0),
(115, 'asdca', NULL, 0),
(116, 'asas', NULL, 0),
(117, 'weaaaa', NULL, 0),
(118, 'si trabaja', NULL, 0),
(119, 'si trabaja', NULL, 0),
(120, 'si trabaja', NULL, 0),
(121, 'si trabaja', NULL, 0),
(122, 'sfvs', NULL, 0),
(123, 'sdsd', NULL, 0),
(124, 'sfvs', NULL, 0),
(125, 'sfvs', NULL, 0),
(126, 'sdsd', NULL, 0),
(127, 'sfvs', NULL, 0),
(128, 'sfvs', NULL, 0),
(129, 'sdsd', NULL, 0),
(130, 'sfvs', NULL, 0),
(131, 'no tiene', NULL, 0),
(132, 'muchas', NULL, 0),
(133, 'xdd', NULL, 0),
(134, 'weaa', NULL, 0),
(135, 'sisisi', NULL, 0),
(136, 'eomaodnmc', NULL, 0),
(137, 'weaa', NULL, 0),
(138, 'sisisi', NULL, 0),
(139, 'eomaodnmc', NULL, 0),
(140, 'ya pe mano', NULL, 0),
(141, 'xdddd', NULL, 0),
(142, 'respuesta32', NULL, 0),
(143, 'varias', NULL, 0),
(144, 'respuesta 18', NULL, 0),
(145, 'prueba de prueba', NULL, 0),
(146, 'yeaa', NULL, 0),
(147, 'sigg', NULL, 0),
(148, 'si hace', NULL, 0);

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
(1, 1),
(7, 4),
(7, 8),
(7, 9);

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
('jHQDoF2F7RjnqMiliO7HDQry7xO5chV9uRWqpEV6', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36 OPR/107.0.0.0', 'YTo2OntzOjM6InVybCI7YTowOnt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo2OiJfdG9rZW4iO3M6NDA6Ind3WTEzbUJURG43elkwUHVjcldHenNpVVpQYkR0OW1RVzlrbE51OUwiO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoyMToicGFzc3dvcmRfaGFzaF9zYW5jdHVtIjtzOjYwOiIkMnkkMTIkeEc4aFU3dUhsTHhzdFR3dTlOdDdidTBwL25jZzV5OTIuRVIweWg0dkNnQXBYVlJyOHVjWi4iO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZW1wcmVzYS9wZXJzb25hbC8yNCI7fX0=', 1715080240);

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
(1, 'admin', 'admin@gmail.com', NULL, NULL, '$2y$12$xG8hU7uHlLxstTwu9Nt7bu0p/ncg5y92.ER0yh4vCgApXVRr8ucZ.', NULL, NULL, NULL, 'fcFcm0IcOs0K7aSDmErfSReyCUGJ8jJLc7jSfwFQAsSy3peJMsQOg6oXHrku', NULL, NULL, '2024-03-20 07:10:54', '2024-03-20 07:10:54'),
(4, 'frank', 'frank@gmail.com', 12, NULL, '$2y$12$txiC1tONdS/gt7I21/UpJOWWhiW40PZor6rI9Yn7fg8OsuC0iGOkC', NULL, NULL, NULL, 'JMf0ODxVNJckqatM3aQxZwnTbCRtdaY9A5NaB9AGlvZC3JFPDqDLE3L4hRRG', NULL, NULL, '2024-04-28 14:24:35', '2024-04-28 14:24:35'),
(5, 'prueba', 'prueba@gmail.com', NULL, NULL, '$2y$12$N2osfbmWpSWE9NNz9NI9h.dzE8UR0aK8N2frLARe.CGcHB6ij72dm', NULL, NULL, NULL, NULL, NULL, NULL, '2024-04-28 14:35:04', '2024-04-28 14:35:04'),
(6, 'alex', 'alex@gmail.com', NULL, NULL, '$2y$12$XoJOmuLFEDFUbmZZVeXW9OeDrZSP49nTZdXsB.I0szEzWnrQ6.8LO', NULL, NULL, NULL, 'NDzocEzmZXce798hBCVRMmk88xfKsLvofaXFO66ffzZbFloxdSLUbjwRfiwL', NULL, NULL, '2024-04-30 02:25:46', '2024-04-30 02:25:46'),
(7, 'prueba2', 'prueba2@gmail.com', 23, NULL, '$2y$12$MsVIUircWhLV6KGHPXyd5Ob1J4fZ0Vo0/P3VQV1/PgDnqbu58G1Sa', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-02 22:24:32', '2024-05-02 22:41:57'),
(8, 'martin', 'martin@gmail.com', 22, NULL, '$2y$12$PpMLnhBhFVh0RKMAvLERH.vGXxxgMJWkyBbSnLhtm6Rrl/fyQx9pu', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-04 01:12:50', '2024-05-04 01:12:50'),
(9, 'Jose', 'jose@gmail.com', 22, NULL, '$2y$12$9./QgImVooY/mV7qdKlCxuF5MuVXMiT4OSymjJpjWk50YaATjgHE2', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-04 07:42:38', '2024-05-04 07:42:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vinculos`
--

CREATE TABLE `vinculos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vinculos`
--

INSERT INTO `vinculos` (`id`, `nombre`) VALUES
(1, 'Auto Evaluación'),
(2, 'Reporte Directo'),
(3, 'Par'),
(4, 'Cliente'),
(5, 'Colega'),
(6, 'Jefe Directo'),
(7, 'Jefe Matricial'),
(8, 'Jefe');

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
  ADD KEY `encuesta_id` (`encuesta`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `cruds`
--
ALTER TABLE `cruds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `detalle_preguntas`
--
ALTER TABLE `detalle_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=427;

--
-- AUTO_INCREMENT de la tabla `envios`
--
ALTER TABLE `envios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=231;

--
-- AUTO_INCREMENT de la tabla `evaluados`
--
ALTER TABLE `evaluados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=483;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `personals`
--
ALTER TABLE `personals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `persona_respuestas`
--
ALTER TABLE `persona_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1499;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  ADD CONSTRAINT `envios_ibfk_2` FOREIGN KEY (`encuesta`) REFERENCES `encuestas` (`id`);

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
