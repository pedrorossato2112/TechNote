<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (($_SESSION['usuario_tipo'] ?? '') !== 'admin' || empty($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}
require_once '../conexao.php';
$conn->set_charset('utf8mb4');
if (empty($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}

$entidades = ['notebooks' => 'Notebooks', 'marcas' => 'Marcas', 'categorias' => 'Categorias'];
$entidade = (string) ($_GET['entidade'] ?? $_POST['entidade'] ?? 'notebooks');
if (!isset($entidades[$entidade])) {
    http_response_code(404);
    exit('Cadastro inexistente.');
}
$erro = '';
$aviso = (string) ($_SESSION['aviso_admin'] ?? '');
unset($_SESSION['aviso_admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_admin'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Sessão expirada. Recarregue a página.');
    }
    $acao = (string) ($_POST['acao'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    try {
        if ($acao === 'excluir') {
            if (!$id) { throw new InvalidArgumentException('Registro inválido.'); }
            $stmt = $conn->prepare("DELETE FROM $entidade WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            if ($stmt->affected_rows === 0) { throw new RuntimeException('Registro não encontrado.'); }
            $_SESSION['aviso_admin'] = 'Registro excluído com sucesso.';
        } elseif ($acao === 'salvar') {
            $nome = trim((string) ($_POST['nome'] ?? ''));
            if ($nome === '' || mb_strlen($nome) > 150) {
                throw new InvalidArgumentException('Informe um nome com até 150 caracteres.');
            }
            if ($entidade === 'notebooks') {
                $precoTexto = str_replace(',', '.', trim((string) ($_POST['preco'] ?? '')));
                $preco = filter_var($precoTexto, FILTER_VALIDATE_FLOAT);
                $estoque = filter_var($_POST['estoque'] ?? null, FILTER_VALIDATE_INT);
                $marcaId = filter_var($_POST['marca_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($preco === false || $preco <= 0 || $estoque === false || $estoque < 0 || !$marcaId) {
                    throw new InvalidArgumentException('Informe marca, preço positivo e estoque não negativo.');
                }
                $categorias = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['categorias'] ?? [])), static fn($valor) => $valor > 0)));
                $conn->begin_transaction();
                if ($id) {
                    $stmt = $conn->prepare('UPDATE notebooks SET nome=?, marca_id=?, preco=?, estoque=? WHERE id=?');
                    $stmt->bind_param('sidii', $nome, $marcaId, $preco, $estoque, $id);
                    $stmt->execute();
                    $check = $conn->prepare('SELECT id FROM notebooks WHERE id=?');
                    $check->bind_param('i', $id);
                    $check->execute();
                    if (!$check->get_result()->fetch_assoc()) { throw new RuntimeException('Notebook não encontrado.'); }
                    $limpar = $conn->prepare('DELETE FROM notebook_categorias WHERE notebook_id=?');
                    $limpar->bind_param('i', $id);
                    $limpar->execute();
                } else {
                    $stmt = $conn->prepare('INSERT INTO notebooks (nome,marca_id,preco,estoque) VALUES (?,?,?,?)');
                    $stmt->bind_param('sidi', $nome, $marcaId, $preco, $estoque);
                    $stmt->execute();
                    $id = $conn->insert_id;
                }
                foreach ($categorias as $categoriaId) {
                    $vinculo = $conn->prepare('INSERT INTO notebook_categorias (notebook_id,categoria_id) VALUES (?,?)');
                    $vinculo->bind_param('ii', $id, $categoriaId);
                    $vinculo->execute();
                }
                $conn->commit();
            } elseif ($id) {
                $stmt = $conn->prepare("UPDATE $entidade SET nome=? WHERE id=?");
                $stmt->bind_param('si', $nome, $id);
                $stmt->execute();
            } else {
                $stmt = $conn->prepare("INSERT INTO $entidade (nome) VALUES (?)");
                $stmt->bind_param('s', $nome);
                $stmt->execute();
            }
            $_SESSION['aviso_admin'] = 'Registro salvo com sucesso.';
        } else {
            throw new InvalidArgumentException('Ação inválida.');
        }
        header('Location: cadastros.php?entidade=' . urlencode($entidade));
        exit;
    } catch (Throwable $e) {
        try { $conn->rollback(); } catch (Throwable $ignored) {}
        $erro = $e instanceof mysqli_sql_exception
            ? 'Não foi possível concluir: nome duplicado, referência inexistente ou registro associado a outro dado.'
            : $e->getMessage();
    }
}

$marcas = $conn->query('SELECT id,nome FROM marcas ORDER BY nome')->fetch_all(MYSQLI_ASSOC);
$categoriasLista = $conn->query('SELECT id,nome FROM categorias ORDER BY nome')->fetch_all(MYSQLI_ASSOC);
$registro = null;
$editarId = filter_var($_GET['editar'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($editarId) {
    $stmt = $conn->prepare("SELECT * FROM $entidade WHERE id=?");
    $stmt->bind_param('i', $editarId);
    $stmt->execute();
    $registro = $stmt->get_result()->fetch_assoc();
    if (!$registro) { $erro = 'Registro não encontrado.'; }
}
$marcadas = [];
if ($entidade === 'notebooks' && $registro) {
    $stmt = $conn->prepare('SELECT categoria_id FROM notebook_categorias WHERE notebook_id=?');
    $stmt->bind_param('i', $editarId);
    $stmt->execute();
    $marcadas = array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'categoria_id'));
}
$linhas = $conn->query($entidade === 'notebooks'
    ? 'SELECT n.id,n.nome,n.preco,n.estoque,COALESCE(m.nome,"Sem marca") AS marca FROM notebooks n LEFT JOIN marcas m ON m.id=n.marca_id ORDER BY n.id DESC'
    : "SELECT id,nome FROM $entidade ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
function h($valor): string { return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cadastros | TechNote</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light"><main class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-4"><h1>Cadastros administrativos</h1><a class="btn btn-outline-light" href="dashboard.php">Voltar à dashboard</a></div>
<nav class="nav nav-pills gap-2 mb-4"><?php foreach ($entidades as $chave => $titulo): ?><a class="nav-link <?= $chave === $entidade ? 'active' : 'text-light' ?>" href="?entidade=<?= h($chave) ?>"><?= h($titulo) ?></a><?php endforeach; ?></nav>
<?php if ($erro): ?><div class="alert alert-danger"><?= h($erro) ?></div><?php endif; ?>
<?php if ($aviso): ?><div class="alert alert-success"><?= h($aviso) ?></div><?php endif; ?>
<section class="card mb-4"><div class="card-body"><h2 class="h4"><?= $registro ? 'Editar' : 'Novo' ?> <?= h($entidades[$entidade]) ?></h2>
<form method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_admin']) ?>"><input type="hidden" name="entidade" value="<?= h($entidade) ?>"><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= h($registro['id'] ?? '') ?>">
<label class="form-label" for="nome">Nome</label><input class="form-control mb-3" id="nome" name="nome" maxlength="150" required value="<?= h($registro['nome'] ?? '') ?>">
<?php if ($entidade === 'notebooks'): ?>
<label class="form-label" for="marca">Marca</label><select class="form-select mb-3" id="marca" name="marca_id" required><option value="">Selecione</option><?php foreach ($marcas as $marca): ?><option value="<?= (int)$marca['id'] ?>" <?= (int)($registro['marca_id'] ?? 0) === (int)$marca['id'] ? 'selected' : '' ?>><?= h($marca['nome']) ?></option><?php endforeach; ?></select>
<div class="row"><div class="col-md-6"><label class="form-label" for="preco">Preço (R$)</label><input class="form-control mb-3" id="preco" type="number" name="preco" min="0.01" step="0.01" required value="<?= h($registro['preco'] ?? '') ?>"></div><div class="col-md-6"><label class="form-label" for="estoque">Estoque</label><input class="form-control mb-3" id="estoque" type="number" name="estoque" min="0" step="1" required value="<?= h($registro['estoque'] ?? 0) ?>"></div></div>
<fieldset class="mb-3"><legend class="fs-6">Categorias</legend><?php foreach ($categoriasLista as $categoria): ?><label class="me-3"><input type="checkbox" name="categorias[]" value="<?= (int)$categoria['id'] ?>" <?= in_array((int)$categoria['id'], $marcadas, true) ? 'checked' : '' ?>> <?= h($categoria['nome']) ?></label><?php endforeach; ?></fieldset>
<?php endif; ?><button class="btn btn-primary">Salvar</button> <?php if ($registro): ?><a class="btn btn-secondary" href="?entidade=<?= h($entidade) ?>">Cancelar edição</a><?php endif; ?></form></div></section>
<section class="card"><div class="card-body"><h2 class="h4">Registros</h2><div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>ID</th><th>Nome</th><?php if ($entidade === 'notebooks'): ?><th>Marca</th><th>Preço</th><th>Estoque</th><?php endif; ?><th>Ações</th></tr></thead><tbody>
<?php foreach ($linhas as $linha): ?><tr><td><?= (int)$linha['id'] ?></td><td><?= h($linha['nome']) ?></td><?php if ($entidade === 'notebooks'): ?><td><?= h($linha['marca']) ?></td><td>R$ <?= number_format((float)$linha['preco'],2,',','.') ?></td><td><?= (int)$linha['estoque'] ?></td><?php endif; ?><td><a class="btn btn-sm btn-outline-primary" href="?entidade=<?= h($entidade) ?>&amp;editar=<?= (int)$linha['id'] ?>">Editar</a> <form method="post" class="d-inline" onsubmit="return confirm('Excluir este registro? Esta ação não pode ser desfeita.')"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_admin']) ?>"><input type="hidden" name="entidade" value="<?= h($entidade) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int)$linha['id'] ?>"><button class="btn btn-sm btn-outline-danger">Excluir</button></form></td></tr><?php endforeach; ?>
<?php if (!$linhas): ?><tr><td colspan="6">Nenhum registro cadastrado.</td></tr><?php endif; ?></tbody></table></div></div></section>
</main></body></html>
