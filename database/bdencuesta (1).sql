-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-04-2024 a las 06:04:20
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
        WHERE e.encuesta = encuesta_id AND e.estado = 1 and r.estado = 1
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
      texto,
      ''' THEN 1 ELSE 0 END) AS `',
      REPLACE(texto, ' ', '_'),
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
    WHERE e.encuesta = encuesta_id AND e.estado = 1 
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
  WHERE e.encuesta = ', encuesta_id, ' AND e.estado = 1 and r.estado = 1 
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
  WHERE e.encuesta = ', encuesta_id, ' AND e.estado = 1 and r.estado = 1 
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
      texto,
      ''' THEN 1 ELSE 0 END) AS `',
      REPLACE(texto, ' ', '_'),
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
    WHERE e.encuesta = encuesta_id AND e.estado = 1 AND pre.categoria = categoria_id 
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
  WHERE e.encuesta = ', encuesta_id, ' AND e.estado = 1 and r.estado = 1 AND pre.categoria = ', categoria_id, ' 
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
  WHERE e.encuesta = ', encuesta_id, ' AND e.estado = 1 and r.estado = 1 AND pre.categoria = ', categoria_id, '
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
                texto,
                ''' THEN 1 ELSE 0 END) AS `',
                REPLACE(texto, ' ', '_'),
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
        WHERE e.encuesta = encuesta_id AND e.estado = 1 AND pre.id = pregunta_id 
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
      WHERE e.encuesta = ', encuesta_id, ' AND e.estado = 1 and r.estado = 1 AND pre.id = ', pregunta_id, ' 
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
      WHERE e.encuesta = ', encuesta_id, ' AND e.estado = 1 and r.estado = 1 AND pre.id = ', pregunta_id, '
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
        WHERE e.encuesta = encuesta_id AND e.estado = 1 and r.estado = 1
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
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id`, `nombre`) VALUES
(1, 'Jefe de área'),
(2, 'Gerente'),
(3, 'Desarrollador');

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
(1, 'Empresa', 'App\\Models\\Empresa', 'empresa', 'fa fa-building', 1, 1, 1, 1, '2024-03-20 07:56:59', '2024-04-02 16:05:47'),
(2, 'Cargo', 'App\\Models\\Cargo', 'cargo', 'fa fa-address-card', 1, 1, 1, 1, '2024-03-20 11:52:19', '2024-04-01 02:07:45'),
(4, 'Personal', 'App\\Models\\Personal', 'personal', 'fa fa-user', 0, 1, 1, 1, '2024-03-20 12:17:15', '2024-04-06 07:41:43'),
(8, 'Respuesta', 'App\\Models\\Respuesta', 'respuesta', 'fa fa-comments', 1, 1, 1, 1, '2024-03-22 11:29:00', '2024-04-01 02:07:45'),
(9, 'Pregunta', 'App\\Models\\Pregunta', 'pregunta', 'fa fa-question', 1, 1, 1, 1, '2024-03-22 11:41:04', '2024-04-06 16:26:49'),
(11, 'Encuesta', 'App\\Models\\Encuesta', 'encuesta', 'fa fa-file', 1, 1, 1, 1, '2024-03-23 10:07:55', '2024-04-01 02:07:45'),
(12, 'Envio', 'App\\Models\\Envio', 'envio', 'fa fa-paper-plane', 1, 1, 1, 1, '2024-03-26 09:51:02', '2024-04-01 02:07:45'),
(13, 'Categoria', 'App\\Models\\Categoria', 'categoria', 'fa fa-bars', 1, 1, 1, 1, '2024-03-28 11:00:07', '2024-04-01 02:07:45'),
(14, 'Vinculo', 'App\\Models\\Vinculo', 'vinculo', 'fa fa-link', 1, 1, 1, 1, '2024-04-07 02:08:34', '2024-04-07 02:11:31');

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
(49, 12);

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
(16, 32, 1),
(17, 32, 2),
(18, 32, 3),
(19, 32, 4),
(20, 32, 5),
(21, 33, 1),
(22, 33, 2),
(23, 33, 3),
(24, 33, 4),
(25, 33, 5),
(26, 34, 1),
(27, 34, 2),
(28, 34, 3),
(29, 34, 4),
(30, 34, 5),
(31, 35, 1),
(32, 35, 2),
(33, 35, 3),
(34, 35, 4),
(35, 35, 5),
(36, 36, 1),
(37, 36, 2),
(38, 36, 3),
(39, 36, 4),
(40, 36, 5),
(41, 37, 1),
(42, 37, 2),
(43, 37, 3),
(44, 37, 4),
(45, 37, 5),
(46, 38, 1),
(47, 38, 2),
(48, 38, 3),
(49, 38, 4),
(50, 38, 5),
(51, 39, 1),
(52, 39, 2),
(53, 39, 3),
(54, 39, 4),
(55, 39, 5),
(56, 40, 1),
(57, 40, 2),
(58, 40, 3),
(59, 40, 4),
(60, 40, 5),
(61, 41, 1),
(62, 41, 2),
(63, 41, 3),
(64, 41, 4),
(65, 41, 5),
(66, 42, 1),
(67, 42, 2),
(68, 42, 3),
(69, 42, 4),
(70, 42, 5),
(71, 43, 1),
(72, 43, 2),
(73, 43, 3),
(74, 43, 4),
(75, 43, 5),
(76, 44, 1),
(77, 44, 2),
(78, 44, 3),
(79, 44, 4),
(80, 44, 5),
(81, 45, 1),
(82, 45, 2),
(83, 45, 3),
(84, 45, 4),
(85, 45, 5),
(91, 49, NULL),
(101, 49, 47),
(109, 49, 50),
(112, 49, 53),
(115, 49, 56),
(118, 49, 59),
(121, 49, 62),
(124, 49, 65),
(92, 50, NULL),
(102, 50, 48),
(110, 50, 51),
(113, 50, 54),
(116, 50, 57),
(119, 50, 60),
(122, 50, 63),
(125, 50, 66),
(93, 51, NULL),
(103, 51, 49),
(111, 51, 52),
(114, 51, 55),
(117, 51, 58),
(120, 51, 61),
(123, 51, 64),
(126, 51, 67),
(104, 52, 1),
(105, 52, 2),
(106, 52, 3),
(107, 52, 4),
(108, 52, 5);

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
(14, '20513613009', 'SOFTWARE ENTERPRISE SERVICES SOCIEDAD ANONIMA CERRADA', 'JR. SANTA ROSA NRO. 191 INT. 206 LIMA LIMA LIMA', NULL, 1),
(22, '20611644940', 'TARMA & HERNANDEZ INVERSIONES E.I.R.L.', '---- LAS PAMPAS MZA. U LOTE. 22 H.U. SOL DE PIMENTEL LAMBAYEQUE CHICLAYO PIMENTEL', 'OCROSPOMA UGAZ FRANK ANTHONY', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas`
--

CREATE TABLE `encuestas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `empresa` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `encuestas`
--

INSERT INTO `encuestas` (`id`, `nombre`, `empresa`, `fecha`) VALUES
(51, 'Evaluación a Edu', 12, '2024-04-10'),
(54, 'Evaluación a Frank', 12, '2024-04-10'),
(55, 'Evaluación a Alex', 12, '2024-04-11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta_preguntas`
--

CREATE TABLE `encuesta_preguntas` (
  `encuesta_id` int(11) NOT NULL,
  `detalle_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `encuesta_preguntas`
--

INSERT INTO `encuesta_preguntas` (`encuesta_id`, `detalle_id`) VALUES
(51, 11),
(51, 12),
(51, 13),
(51, 14),
(51, 15),
(51, 16),
(51, 17),
(51, 18),
(51, 19),
(51, 20),
(51, 21),
(51, 22),
(51, 23),
(51, 24),
(51, 25),
(51, 26),
(51, 27),
(51, 28),
(51, 29),
(51, 30),
(51, 31),
(51, 32),
(51, 33),
(51, 34),
(51, 35),
(51, 36),
(51, 37),
(51, 38),
(51, 39),
(51, 40),
(51, 41),
(51, 42),
(51, 43),
(51, 44),
(51, 45),
(51, 46),
(51, 47),
(51, 48),
(51, 49),
(51, 50),
(51, 51),
(51, 52),
(51, 53),
(51, 54),
(51, 55),
(51, 56),
(51, 57),
(51, 58),
(51, 59),
(51, 60),
(51, 61),
(51, 62),
(51, 63),
(51, 64),
(51, 65),
(51, 66),
(51, 67),
(51, 68),
(51, 69),
(51, 70),
(51, 71),
(51, 72),
(51, 73),
(51, 74),
(51, 75),
(51, 76),
(51, 77),
(51, 78),
(51, 79),
(51, 80),
(51, 81),
(51, 82),
(51, 83),
(51, 84),
(51, 85),
(51, 91),
(51, 92),
(51, 93),
(51, 101),
(51, 102),
(51, 103),
(51, 104),
(51, 105),
(51, 106),
(51, 107),
(51, 108),
(51, 109),
(51, 110),
(51, 111),
(51, 112),
(51, 113),
(51, 114),
(51, 115),
(51, 116),
(51, 117),
(51, 118),
(51, 119),
(51, 120),
(51, 124),
(51, 125),
(51, 126),
(54, 11),
(54, 12),
(54, 13),
(54, 14),
(54, 15),
(54, 16),
(54, 17),
(54, 18),
(54, 19),
(54, 20),
(55, 11),
(55, 12),
(55, 13),
(55, 14),
(55, 15),
(55, 16),
(55, 17),
(55, 18),
(55, 19),
(55, 20),
(55, 21),
(55, 22),
(55, 23),
(55, 24),
(55, 25),
(55, 26),
(55, 27),
(55, 28),
(55, 29),
(55, 30),
(55, 31),
(55, 32),
(55, 33),
(55, 34),
(55, 35),
(55, 36),
(55, 37),
(55, 38),
(55, 39),
(55, 40),
(55, 41),
(55, 42),
(55, 43),
(55, 44),
(55, 45),
(55, 46),
(55, 47),
(55, 48),
(55, 49),
(55, 50),
(55, 51),
(55, 52),
(55, 53),
(55, 54),
(55, 55),
(55, 56),
(55, 57),
(55, 58),
(55, 59),
(55, 60),
(55, 61),
(55, 62),
(55, 63),
(55, 64),
(55, 65),
(55, 66),
(55, 67),
(55, 68),
(55, 69),
(55, 70),
(55, 71),
(55, 72),
(55, 73),
(55, 74),
(55, 75),
(55, 76),
(55, 77),
(55, 78),
(55, 79),
(55, 80),
(55, 81),
(55, 82),
(55, 83),
(55, 84),
(55, 85),
(55, 91),
(55, 92),
(55, 93),
(55, 101),
(55, 102),
(55, 103),
(55, 104),
(55, 105),
(55, 106),
(55, 107),
(55, 108),
(55, 109),
(55, 110),
(55, 111),
(55, 112),
(55, 113),
(55, 114),
(55, 115),
(55, 116),
(55, 117),
(55, 118),
(55, 119),
(55, 120),
(55, 121),
(55, 122),
(55, 123);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios`
--

CREATE TABLE `envios` (
  `id` int(11) NOT NULL,
  `persona` int(11) DEFAULT NULL,
  `encuesta` int(11) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `rango` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `envios`
--

INSERT INTO `envios` (`id`, `persona`, `encuesta`, `estado`, `uuid`, `rango`) VALUES
(61, 42, 51, 1, '2227df63-5aeb-45ee-98f5-1a16aa4d29ed', 3.13),
(62, 43, 51, 1, 'dae17bf5-b24b-4309-8b23-391e8deacbde', 3.13),
(63, 47, 51, 1, 'e112250b-5539-4210-a7ee-1b7f9e22fdfd', 4.13),
(68, 42, 54, 0, 'ffe8bc17-4c37-4b46-8f9e-5731c6d8cf6d', NULL),
(69, 43, 54, 0, 'c9e7c36f-f867-4c58-9399-6add749bc084', NULL),
(70, 45, 55, 0, 'f8920f3f-6625-4876-83bd-223f5359f76e', NULL),
(71, 42, 55, 1, 'cc866ab8-f84e-4480-b2af-5c50ea46aea8', 3.69);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluados`
--

CREATE TABLE `evaluados` (
  `id` int(11) NOT NULL,
  `evaluado_id` int(11) NOT NULL,
  `evaluador_id` int(11) NOT NULL,
  `encuesta_id` int(11) NOT NULL,
  `vinculo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evaluados`
--

INSERT INTO `evaluados` (`id`, `evaluado_id`, `evaluador_id`, `encuesta_id`, `vinculo_id`) VALUES
(84, 43, 42, 51, 2),
(85, 43, 43, 51, 1),
(86, 43, 47, 51, 3),
(91, 42, 42, 54, 1),
(92, 42, 43, 54, 3),
(93, 45, 45, 55, 1),
(94, 45, 42, 55, 3);

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
  `dni` varchar(20) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `cargo` int(11) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personals`
--

INSERT INTO `personals` (`id`, `dni`, `nombre`, `correo`, `telefono`, `cargo`, `estado`) VALUES
(42, '77231754', 'OCROSPOMA UGAZ FRANK ANTHONY', 'frankocrospomaugaz@gmail.com', '920532729', 1, 1),
(43, '72050992', 'FERNANDEZ ALVA EDU', 'frankocrospomaugaz@gmail.com', '987546213', 3, 1),
(45, '47071856', 'SAMAME NIZAMA JOSE ALEXANDER', 'alex_3849@hotmail.com', '956930067', 1, 1),
(46, '16734323', 'AMPUERO PASCO GILBERTO MARTIN', 'martinampuero@hotmail.com', '958746123', 2, 1),
(47, '75010274', 'GUZMAN MORI CARLOS GUSTAVO', 'frankocrospomaugaz@gmail.com', '985472163', 3, 1),
(49, '75376346', 'GIL FERNANDEZ GEANCARLOS', 'zcuak1221@gmail.com', '958741263', 3, 1);

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
(886, 43, 14, 51),
(887, 43, 17, 51),
(888, 43, 24, 51),
(889, 43, 30, 51),
(890, 43, 35, 51),
(891, 43, 38, 51),
(892, 43, 41, 51),
(893, 43, 46, 51),
(894, 43, 53, 51),
(895, 43, 60, 51),
(896, 43, 61, 51),
(897, 43, 70, 51),
(898, 43, 73, 51),
(899, 43, 77, 51),
(900, 43, 85, 51),
(901, 43, 104, 51),
(902, 43, 115, 51),
(903, 43, 116, 51),
(904, 43, 117, 51),
(905, 47, 13, 51),
(906, 47, 20, 51),
(907, 47, 25, 51),
(908, 47, 29, 51),
(909, 47, 35, 51),
(910, 47, 36, 51),
(911, 47, 42, 51),
(912, 47, 50, 51),
(913, 47, 55, 51),
(914, 47, 60, 51),
(915, 47, 65, 51),
(916, 47, 70, 51),
(917, 47, 75, 51),
(918, 47, 80, 51),
(919, 47, 85, 51),
(920, 47, 104, 51),
(921, 47, 118, 51),
(922, 47, 119, 51),
(923, 47, 120, 51),
(924, 42, 14, 55),
(925, 42, 17, 55),
(926, 42, 24, 55),
(927, 42, 27, 55),
(928, 42, 35, 55),
(929, 42, 40, 55),
(930, 42, 45, 55),
(931, 42, 49, 55),
(932, 42, 54, 55),
(933, 42, 59, 55),
(934, 42, 61, 55),
(935, 42, 70, 55),
(936, 42, 73, 55),
(937, 42, 80, 55),
(938, 42, 83, 55),
(939, 42, 106, 55),
(940, 42, 121, 55),
(941, 42, 122, 55),
(942, 42, 123, 55),
(943, 42, 12, 51),
(944, 42, 20, 51),
(945, 42, 25, 51),
(946, 42, 28, 51),
(947, 42, 33, 51),
(948, 42, 37, 51),
(949, 42, 41, 51),
(950, 42, 46, 51),
(951, 42, 55, 51),
(952, 42, 58, 51),
(953, 42, 61, 51),
(954, 42, 70, 51),
(955, 42, 74, 51),
(956, 42, 78, 51),
(957, 42, 84, 51),
(958, 42, 106, 51),
(959, 42, 124, 51),
(960, 42, 125, 51),
(961, 42, 126, 51);

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
(67, 'sdvasd', NULL, 0);

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
(1, 'super_admin', 'a:1:{s:10:\"fullAccess\";i:1;}', '2024-03-20 07:56:29', '2024-03-20 07:56:29');

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
('OHxsCATcGwAroekkF9vA3i5yRpPnOdGN6arAMAY9', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36 OPR/107.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQ2dKTm5abFVNVFFVY01QYThaMjNnaTFYVTFxcWVvTTQ1TjVtSWR3VSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9lbmN1ZXN0YXBkZi81MSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1713085495);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
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

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gmail.com', NULL, '$2y$12$xG8hU7uHlLxstTwu9Nt7bu0p/ncg5y92.ER0yh4vCgApXVRr8ucZ.', NULL, NULL, NULL, 'zMqPZAg0SArUSalMJSp4MVT00tQdG9QpTBbasYFaFUSmgTTx06pG92PILgdM', NULL, NULL, '2024-03-20 07:10:54', '2024-03-20 07:10:54');

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
(4, 'Cliente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `empresa` (`empresa`);

--
-- Indices de la tabla `encuesta_preguntas`
--
ALTER TABLE `encuesta_preguntas`
  ADD PRIMARY KEY (`encuesta_id`,`detalle_id`),
  ADD KEY `encuesta_id` (`encuesta_id`,`detalle_id`),
  ADD KEY `id` (`encuesta_id`,`detalle_id`) USING BTREE,
  ADD KEY `fk_pregunta_id` (`detalle_id`);

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
  ADD KEY `vinculo_id` (`vinculo_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `cargo` (`cargo`);

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
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `vinculos`
--
ALTER TABLE `vinculos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `cruds`
--
ALTER TABLE `cruds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `detalle_preguntas`
--
ALTER TABLE `detalle_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `envios`
--
ALTER TABLE `envios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de la tabla `evaluados`
--
ALTER TABLE `evaluados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `personals`
--
ALTER TABLE `personals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `persona_respuestas`
--
ALTER TABLE `persona_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=962;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vinculos`
--
ALTER TABLE `vinculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  ADD CONSTRAINT `fk_empresa_id` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`);

--
-- Filtros para la tabla `encuesta_preguntas`
--
ALTER TABLE `encuesta_preguntas`
  ADD CONSTRAINT `fk_encuesta_id` FOREIGN KEY (`encuesta_id`) REFERENCES `encuestas` (`id`),
  ADD CONSTRAINT `fk_pregunta_id` FOREIGN KEY (`detalle_id`) REFERENCES `detalle_preguntas` (`id`);

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
  ADD CONSTRAINT `evaluados_ibfk_4` FOREIGN KEY (`vinculo_id`) REFERENCES `vinculos` (`id`);

--
-- Filtros para la tabla `panel_admins`
--
ALTER TABLE `panel_admins`
  ADD CONSTRAINT `panel_admins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `personals`
--
ALTER TABLE `personals`
  ADD CONSTRAINT `personals_ibfk_2` FOREIGN KEY (`cargo`) REFERENCES `cargos` (`id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
