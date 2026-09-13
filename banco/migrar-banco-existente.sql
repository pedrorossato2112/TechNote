-- Migração para o banco TechNote anterior (com tabela notebook_categoria).
-- Faça backup antes de executar. Preserva as tabelas e os dados existentes.
USE technote;

CREATE TABLE IF NOT EXISTS notebook_categorias (
  notebook_id INT NOT NULL,
  categoria_id INT NOT NULL,
  PRIMARY KEY (notebook_id,categoria_id),
  CONSTRAINT fk_nc_novo_notebook FOREIGN KEY (notebook_id) REFERENCES notebooks(id) ON DELETE CASCADE,
  CONSTRAINT fk_nc_nova_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO notebook_categorias (notebook_id,categoria_id)
SELECT notebook_id,categoria_id FROM notebook_categoria;

DROP VIEW IF EXISTS vw_vendas_dashboard;
CREATE VIEW vw_vendas_dashboard AS
SELECT p.id AS pedido_id,p.criado_em,pi.notebook_id,
       COALESCE(n.nome,'Produto removido') AS notebook_nome,
       COALESCE(m.nome,'Sem marca') AS marca_nome,
       COALESCE(GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', '),'Sem categoria') AS categorias,
       pi.quantidade,pi.preco_unitario,pi.quantidade*pi.preco_unitario AS subtotal
FROM pedido_itens pi
JOIN pedidos p ON p.id=pi.pedido_id
LEFT JOIN notebooks n ON n.id=pi.notebook_id
LEFT JOIN marcas m ON m.id=n.marca_id
LEFT JOIN notebook_categorias nc ON nc.notebook_id=n.id
LEFT JOIN categorias c ON c.id=nc.categoria_id
GROUP BY pi.id,p.id,p.criado_em,pi.notebook_id,n.nome,m.nome,pi.quantidade,pi.preco_unitario;

DROP VIEW IF EXISTS vw_catalogo_completo;
CREATE VIEW vw_catalogo_completo AS
SELECT n.id,n.nome,COALESCE(m.nome,'Sem marca') AS marca,n.preco,n.estoque,
       COALESCE(GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', '),'Sem categoria') AS categorias
FROM notebooks n
LEFT JOIN marcas m ON m.id=n.marca_id
LEFT JOIN notebook_categorias nc ON nc.notebook_id=n.id
LEFT JOIN categorias c ON c.id=nc.categoria_id
GROUP BY n.id,n.nome,m.nome,n.preco,n.estoque;

DELIMITER $$
DROP TRIGGER IF EXISTS trg_notebooks_before_insert$$
CREATE TRIGGER trg_notebooks_before_insert BEFORE INSERT ON notebooks FOR EACH ROW
BEGIN
  IF NEW.preco <= 0 OR NEW.estoque < 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Preço deve ser positivo e estoque não pode ser negativo';
  END IF;
END$$
DROP TRIGGER IF EXISTS trg_notebooks_before_update$$
CREATE TRIGGER trg_notebooks_before_update BEFORE UPDATE ON notebooks FOR EACH ROW
BEGIN
  IF NEW.preco <= 0 OR NEW.estoque < 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Preço deve ser positivo e estoque não pode ser negativo';
  END IF;
END$$
DROP FUNCTION IF EXISTS fn_subtotal_item$$
CREATE FUNCTION fn_subtotal_item(p_quantidade INT,p_preco DECIMAL(12,2)) RETURNS DECIMAL(12,2)
DETERMINISTIC NO SQL
BEGIN
  RETURN GREATEST(p_quantidade,0)*GREATEST(p_preco,0);
END$$
DROP PROCEDURE IF EXISTS sp_resumo_dashboard$$
CREATE PROCEDURE sp_resumo_dashboard()
READS SQL DATA
BEGIN
  SELECT COUNT(DISTINCT pedido_id) AS total_pedidos,
         COALESCE(SUM(subtotal),0) AS faturamento_itens,
         COALESCE(SUM(quantidade),0) AS unidades_vendidas
  FROM vw_vendas_dashboard;
END$$
DROP PROCEDURE IF EXISTS sp_vendas_paginadas$$
CREATE PROCEDURE sp_vendas_paginadas(IN p_limite INT,IN p_deslocamento INT)
READS SQL DATA
BEGIN
  SELECT * FROM vw_vendas_dashboard ORDER BY criado_em DESC,pedido_id DESC
  LIMIT p_limite OFFSET p_deslocamento;
END$$
DELIMITER ;

WITH vendas_consolidadas AS (
  SELECT notebook_id,notebook_nome,SUM(quantidade) AS unidades,SUM(subtotal) AS receita
  FROM vw_vendas_dashboard GROUP BY notebook_id,notebook_nome
)
SELECT * FROM vendas_consolidadas ORDER BY receita DESC;
