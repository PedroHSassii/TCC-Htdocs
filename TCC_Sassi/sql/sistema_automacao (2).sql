-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 22/07/2025 às 17:38
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_automacao`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `ambientes`
--

CREATE TABLE `ambientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `numero_sala` int(11) NOT NULL,
  `andar` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `predio_id` int(11) DEFAULT NULL,
  `modo` varchar(50) DEFAULT '2',
  `temperatura` int(11) DEFAULT 22,
  `velocidade` varchar(20) DEFAULT 'automatica',
  `swing` tinyint(4) DEFAULT 0,
  `timer` tinyint(4) DEFAULT 0,
  `status` tinyint(4) DEFAULT 0,
  `temp_atual` int(11) NOT NULL,
  `hum_atual` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcoes`
--

CREATE TABLE `funcoes` (
  `cod_tipofunc` int(11) NOT NULL,
  `funcao` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico`
--

CREATE TABLE `historico` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `ambiente_id` int(11) DEFAULT NULL,
  `acao` enum('ligar','desligar') NOT NULL,
  `temperatura` int(11) DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ir_codes`
--

CREATE TABLE `ir_codes` (
  `id` int(11) NOT NULL,
  `cod_ambiente` int(11) NOT NULL,
  `codigo_ir` varchar(50) NOT NULL,
  `modo` varchar(20) DEFAULT NULL,
  `temperatura` int(11) DEFAULT NULL,
  `velocidade` varchar(20) DEFAULT NULL,
  `swing` tinyint(1) DEFAULT NULL,
  `timer` tinyint(1) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `predios`
--

CREATE TABLE `predios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `responsavel_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `temporaria`
--

CREATE TABLE `temporaria` (
  `id` int(11) NOT NULL,
  `leitura` int(11) NOT NULL,
  `alteracao` int(11) NOT NULL,
  `codigo_captado` int(11) NOT NULL,
  `predio` int(11) NOT NULL,
  `sala` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `senha`, `created_at`, `is_admin`, `nome`) VALUES
(1, 'admin@admin.com', '$2y$10$8EqDWkX96H3..4mAcOa8QeMIVMqL8UgAbeXTcZI2IkthS.M1t63Zy', '2025-05-22 23:29:18', 1, ''),
(2, 'user@user.com', '$2y$10$FrbS5VOYIviJKKNSMJWxaup0wTkMVrYyagK8luY7x5C4H4AI3jLvy', '2025-05-23 00:21:03', NULL, ''),
(3, 'teste@teste', '$2y$10$sOvEgFVmthiwpmw07xJUBObaUqsPFJlVMZyl5giEElXnfb0CUiSUi', '2025-05-23 00:58:58', 0, 'Teste'),
(5, 'teste2@teste', '$2y$10$7gH82fJ9SafM1HQytmDs..Sow6x7gqF6MqJROl3NH6TdO7Gu9lXW2', '2025-05-23 01:05:33', 1, 'Teste2');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `ambientes`
--
ALTER TABLE `ambientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `predio_id` (`predio_id`);

--
-- Índices de tabela `funcoes`
--
ALTER TABLE `funcoes`
  ADD PRIMARY KEY (`cod_tipofunc`);

--
-- Índices de tabela `historico`
--
ALTER TABLE `historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `ambiente_id` (`ambiente_id`);

--
-- Índices de tabela `ir_codes`
--
ALTER TABLE `ir_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cod_ambiente` (`cod_ambiente`);

--
-- Índices de tabela `predios`
--
ALTER TABLE `predios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `responsavel_id` (`responsavel_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `ambientes`
--
ALTER TABLE `ambientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `funcoes`
--
ALTER TABLE `funcoes`
  MODIFY `cod_tipofunc` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico`
--
ALTER TABLE `historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ir_codes`
--
ALTER TABLE `ir_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `predios`
--
ALTER TABLE `predios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `ambientes`
--
ALTER TABLE `ambientes`
  ADD CONSTRAINT `ambientes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ambientes_ibfk_2` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `historico`
--
ALTER TABLE `historico`
  ADD CONSTRAINT `historico_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_ibfk_2` FOREIGN KEY (`ambiente_id`) REFERENCES `ambientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ir_codes`
--
ALTER TABLE `ir_codes`
  ADD CONSTRAINT `ir_codes_ibfk_2` FOREIGN KEY (`cod_ambiente`) REFERENCES `ambientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `predios`
--
ALTER TABLE `predios`
  ADD CONSTRAINT `predios_ibfk_1` FOREIGN KEY (`responsavel_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
