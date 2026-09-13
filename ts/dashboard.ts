interface Venda {
    pedidoId: number;
    criadoEm: string;
    produtoId: number;
    produto: string;
    marca: string;
    categorias: string;
    quantidade: number;
    precoUnitario: number;
    subtotal: number;
}

interface ProdutoEstoque {
    id: number;
    nome: string;
    marca: string;
    preco: number;
    estoque: number;
}

interface ResumoBanco {
    totalProdutos: number;
    totalEstoque: number;
    totalClientes: number;
    valorEstoque: number;
}

interface DashboardResposta {
    vendas: Venda[];
    estoque: ProdutoEstoque[];
    resumo: ResumoBanco;
    erro?: string;
}

interface AcumuladorVendas {
    faturamento: number;
    unidadesVendidas: number;
    pedidos: Set<number>;
}

interface ProdutoRanking {
    nome: string;
    quantidade: number;
}

const moeda = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});
let respostaAtual: DashboardResposta | null = null;

function filtrarVendas(vendas: Venda[]): Venda[] {
    const inicio = (document.getElementById('filtro-inicio') as HTMLInputElement | null)?.value ?? '';
    const fim = (document.getElementById('filtro-fim') as HTMLInputElement | null)?.value ?? '';
    const categoria = (document.getElementById('filtro-categoria') as HTMLSelectElement | null)?.value ?? '';
    return vendas.filter((venda) => {
        const data = venda.criadoEm.slice(0, 10);
        const categorias = venda.categorias.split(',').map((nome) => nome.trim());
        return (!inicio || data >= inicio) && (!fim || data <= fim)
            && (!categoria || categorias.includes(categoria));
    });
}

function carregarCategorias(vendas: Venda[]): void {
    const seletor = document.getElementById('filtro-categoria') as HTMLSelectElement | null;
    if (!seletor) return;
    const nomes = [...new Set(vendas.flatMap((venda) => venda.categorias.split(',').map((nome) => nome.trim())))]
        .filter((nome) => nome && nome !== 'Sem categoria').sort();
    seletor.replaceChildren(new Option('Todas', ''), ...nomes.map((nome) => new Option(nome, nome)));
}

function definirTexto(id: string, valor: string): void {
    const elemento = document.getElementById(id);
    if (elemento) {
        elemento.textContent = valor;
    }
}

function escaparHtml(valor: string): string {
    const elemento = document.createElement('div');
    elemento.textContent = valor;
    return elemento.innerHTML;
}

function calcularIndicadores(vendas: Venda[]): {
    faturamento: number;
    unidadesVendidas: number;
    totalPedidos: number;
    ticketMedio: number;
} {
    const acumulado = vendas.reduce<AcumuladorVendas>(
        (resultado, venda) => {
            resultado.faturamento += venda.quantidade * venda.precoUnitario;
            resultado.unidadesVendidas += venda.quantidade;
            resultado.pedidos.add(venda.pedidoId);
            return resultado;
        },
        { faturamento: 0, unidadesVendidas: 0, pedidos: new Set<number>() },
    );

    const totalPedidos = acumulado.pedidos.size;
    const ticketMedio = totalPedidos > 0 ? acumulado.faturamento / totalPedidos : 0;

    return {
        faturamento: acumulado.faturamento,
        unidadesVendidas: acumulado.unidadesVendidas,
        totalPedidos,
        ticketMedio,
    };
}

function gerarRanking(vendas: Venda[]): ProdutoRanking[] {
    const totais = vendas.reduce<Record<number, ProdutoRanking>>((resultado, venda) => {
        const atual = resultado[venda.produtoId] ?? { nome: venda.produto, quantidade: 0 };
        atual.quantidade += venda.quantidade;
        resultado[venda.produtoId] = atual;
        return resultado;
    }, {});

    return Object.values(totais)
        .sort((produtoA, produtoB) => produtoB.quantidade - produtoA.quantidade)
        .slice(0, 5);
}

function renderizarEstoqueCritico(produtos: ProdutoEstoque[]): void {
    const container = document.getElementById('lista-estoque-critico');
    if (!container) {
        return;
    }

    const criticos = produtos.filter((produto) => produto.estoque <= 3).slice(0, 5);
    definirTexto('contador-estoque-critico', String(criticos.length));

    if (criticos.length === 0) {
        container.innerHTML = '<div class="dashboard-empty"><span>✓</span><p>Nenhum produto com estoque crítico.</p></div>';
        return;
    }

    container.innerHTML = `<div class="dashboard-list">${criticos
        .map(
            (produto) => `
                <div class="dashboard-list-item">
                    <div>
                        <strong>${escaparHtml(produto.nome)}</strong>
                        <span>${escaparHtml(produto.marca)}</span>
                    </div>
                    <div class="stock-number">${produto.estoque}<small>un.</small></div>
                </div>`,
        )
        .join('')}</div>`;
}

function renderizarRanking(vendas: Venda[]): void {
    const container = document.getElementById('ranking-produtos');
    if (!container) {
        return;
    }

    const ranking = gerarRanking(vendas);
    if (ranking.length === 0) {
        container.innerHTML = '<div class="dashboard-empty"><span>★</span><p>Nenhuma venda registrada para gerar o ranking.</p></div>';
        return;
    }

    const maiorQuantidade = ranking[0]?.quantidade ?? 0;
    container.innerHTML = `<div class="ranking-list">${ranking
        .map((produto, indice) => {
            const largura = maiorQuantidade > 0 ? (produto.quantidade / maiorQuantidade) * 100 : 0;
            return `
                <div class="ranking-item">
                    <div class="ranking-position">${String(indice + 1).padStart(2, '0')}</div>
                    <div class="ranking-product">
                        <strong>${escaparHtml(produto.nome)}</strong>
                        <span>${produto.quantidade} unidades vendidas</span>
                    </div>
                    <div class="ranking-bar"><div style="width:${largura}%"></div></div>
                </div>`;
        })
        .join('')}</div>`;
}

function renderizarDashboard(dados: DashboardResposta): void {
    const vendasFiltradas = filtrarVendas(dados.vendas);
    const indicadores = calcularIndicadores(vendasFiltradas);
    const estoqueCritico = dados.estoque.filter((produto) => produto.estoque <= 3).length;

    definirTexto('indicador-produtos', String(dados.resumo.totalProdutos));
    definirTexto('indicador-pedidos', String(indicadores.totalPedidos));
    definirTexto('indicador-estoque', String(dados.resumo.totalEstoque));
    definirTexto('indicador-clientes', String(dados.resumo.totalClientes));
    definirTexto('indicador-valor-estoque', moeda.format(dados.resumo.valorEstoque));
    definirTexto('indicador-faturamento', moeda.format(indicadores.faturamento));
    definirTexto('indicador-ticket-medio', moeda.format(indicadores.ticketMedio));
    definirTexto('indicador-estoque-baixo', String(estoqueCritico));

    renderizarEstoqueCritico(dados.estoque);
    renderizarRanking(vendasFiltradas);
    definirTexto('dashboard-api-status', 'DADOS ATUALIZADOS');
}

async function carregarDashboard(): Promise<void> {
    definirTexto('dashboard-api-status', 'ATUALIZANDO...');

    try {
        const resposta = await fetch('../api/dashboard.php', {
            headers: { Accept: 'application/json' },
        });

        const dados = (await resposta.json()) as DashboardResposta;
        if (!resposta.ok) {
            throw new Error(dados.erro ?? 'Falha ao consultar a API.');
        }

        respostaAtual = dados;
        carregarCategorias(dados.vendas);
        renderizarDashboard(dados);
    } catch (erro: unknown) {
        console.error('Falha ao carregar a dashboard:', erro);
        definirTexto('dashboard-api-status', 'DADOS INDISPONÍVEIS');

        const aviso = document.getElementById('dashboard-api-aviso');
        if (aviso) {
            aviso.hidden = false;
            aviso.textContent = 'Não foi possível atualizar os indicadores. Os dados exibidos podem estar desatualizados.';
        }
    }
}

for (const id of ['filtro-inicio', 'filtro-fim', 'filtro-categoria']) {
    document.getElementById(id)?.addEventListener('change', () => {
        if (respostaAtual) renderizarDashboard(respostaAtual);
    });
}
document.getElementById('limpar-filtros')?.addEventListener('click', () => {
    for (const id of ['filtro-inicio', 'filtro-fim', 'filtro-categoria']) {
        const campo = document.getElementById(id) as HTMLInputElement | HTMLSelectElement | null;
        if (campo) campo.value = '';
    }
    if (respostaAtual) renderizarDashboard(respostaAtual);
});

void carregarDashboard();
