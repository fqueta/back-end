<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamento da Matrícula</title>
    <style>
        /* PT: Estilos básicos para o relatório PDF similar à imagem.
           EN: Basic styles to match the provided PDF appearance. */
        :root { --text: #111827; --muted: #6b7280; --border: #e5e7eb; --chip: #f3f4f6; --accent: #ef4444; }
        /* PT: Define tamanho da página A4 e remove margens.
           EN: Set page size to A4 and remove margins. */
        @page { size: A4; margin: 0; }
        /* PT: Reset de body sem definir altura fixa, evitando limitar a paginação.
           EN: Reset body without fixed height to avoid pagination being limited. */
        body { font-family: Arial, Helvetica, sans-serif; color: var(--text); margin: 0; padding: 0; }
        header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
        .client-info { font-size: 12px; line-height: 1.4; }
        .client-info b { font-weight: 700; }
        .meta-info { text-align: right; font-size: 12px; line-height: 1.4; }
        h1 { font-size: 18px; margin: 8px 0 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border-bottom: 1px solid var(--border); padding: 8px; font-size: 12px; }
        th { text-align: left; color: var(--muted); font-weight: 600; }
        tfoot td { font-weight: 700; }
        .right { text-align: right; }
        .muted { color: var(--muted); }
        .accent { color: var(--accent); font-weight: 700; }
        .chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 6px 0 12px; }
        .chip { background: var(--chip); border: 1px solid var(--border); border-radius: 16px; padding: 4px 8px; font-size: 11px; }
        .section-title { font-size: 14px; font-weight: 700; margin: 16px 0 8px; }
        .content-html { font-size: 12px; line-height: 1.55; }
        .check { color: #10b981; font-weight: 700; }
        .footer { margin-top: 18px; font-size: 11px; color: var(--muted); }
        /* PT: Container interno por página.
           - Garante altura de uma folha A4 mesmo sem conteúdo (apenas fundo)
           - Força quebra de página entre blocos .page
           EN: Per-page container.
           - Ensures A4 height even with no text (background-only pages)
           - Forces page breaks between .page blocks */
        .page {
            padding: 0; /* full-bleed background (no padding on page container) */
            box-sizing: border-box;
            page-break-inside: avoid;
            height: 297mm; /* A4 height */
            width: 210mm; /* A4 width ensures full-bleed background */
            page-break-after: always;
            break-after: page; /* modern property */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            position: relative; /* establish containing block for background sizing */
            overflow: hidden; /* ensure absolute bg doesn't overflow */
        }
        /* Function-level comment: Content wrapper inside page to preserve padding without shrinking background. */
        /* PT: Wrapper interno para conteúdo com padding; fundo permanece full-bleed. */
        /* EN: Inner wrapper to provide padding while keeping background full-bleed. */
        .page-inner {
            padding: 24px;
            box-sizing: border-box;
            min-height: 297mm;
            width: 210mm;
            position: relative;
            z-index: 1;
        }
        .page:last-of-type { page-break-after: auto; }
        /* PT: Quebra de página entre containers .page.
           EN: Page break between .page containers. */
        .page + .page { page-break-before: always; break-before: page; }
        @media print {
            .page { page-break-after: always; break-after: page; }
            .page:last-child { page-break-after: auto; }
        }
        /* PT: Preenchedor para páginas extras sem conteúdo textual.
           EN: Filler for extra pages with no textual content. */
        .page-filler { display: block; min-height: 100%; }
        /* PT/EN: Element-based full-bleed background to improve wkhtmltopdf reliability */
        /* PT: Imagem de fundo posicionada atrás do conteúdo usando z-index.
           - Ultrapassa 2mm em cada lado para evitar faixas brancas por arredondamento do wkhtmltopdf
           EN: Background image behind content using z-index.
           - Bleeds 2mm beyond each edge to avoid white bands from wkhtmltopdf rounding */
        .page-bg {
            position: absolute;
            top: -2mm;
            left: -2mm;
            width: calc(210mm + 4mm);
            height: calc(297mm + 4mm);
            object-fit: cover; /* full-bleed */
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="page">
    @if(!empty($background_data_uri) || !empty($background_url))
        <img class="page-bg" src="{{ $background_data_uri ?? $background_url }}" alt="" />
    @endif
    <div class="page-inner">
    <!-- PT: Cabeçalho com dados do cliente e da matrícula | EN: Header with client/enrollment data -->
    <header>
        <div class="client-info">
            <div>Cliente: <b>{{ $cliente_nome }}</b> <span class="muted">zapsint Nº: {{ $cliente_zapsint ?? '-' }}</span></div>
            <div>Telefone: <b>{{ $cliente_telefone ?? '-' }}</b></div>
            <div>Email: <b>{{ $cliente_email ?? '-' }}</b></div>
            <div>Data: <b>{{ $data_formatada }}</b> &nbsp; Validade: <b>{{ $validade_formatada }}</b></div>
        </div>
        <div class="meta-info">
            <div class="muted">CRM • Aeroclube</div>
            <div>Consultor: <b>{{ $consultor_nome ?? '-' }}</b></div>
        </div>
    </header>

    <h1>Orçamento</h1>

    <!-- PT: Tabela de itens do orçamento | EN: Budget items table -->
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Etapa</th>
                <th class="right">H. Teóricas</th>
                <th class="right">H. Práticas</th>
                <th class="right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($orc['modulos'] ?? []) as $m)
                <tr>
                    <td>{{ $m['titulo'] ?? '-' }}</td>
                    <td class="muted">{{ $m['etapa'] ?? '—' }}</td>
                    <td class="right">{{ $m['limite'] ?? '0' }}</td>
                    <td class="right">{{ $m['limite_pratico'] ?? '0' }}</td>
                    <td class="right">{{ $m['valor'] ?? '0,00' }}</td>
                </tr>
            @endforeach
            @if(isset($desconto) && $desconto !== null)
                <tr>
                    <td class="accent">Desconto de Pontualidade</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="right accent">- R$ {{ number_format((float)$desconto, 2, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right">Subtotal</td>
                <td class="right">R$ {{ $subtotal_formatado }}</td>
            </tr>
            <tr>
                <td colspan="4" class="right">Total do Orçamento</td>
                <td class="right">R$ {{ $total_formatado }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- PT: Seção Parcelamento com chips | EN: Installment section with chips -->
    <div class="section-title">Parceliamento</div>
    <div class="chips">
        @php
            /*
             * PT: Normaliza $orc (string JSON ou array) e coleta linhas do parcelamento com segurança.
             * EN: Normalize $orc (JSON string or array) and safely collect installment lines.
             */
            $orcArr = is_array($orc)
                ? $orc
                : (is_string($orc) ? (json_decode($orc, true) ?: []) : []);
            $linhas = [];
            if (isset($orcArr['parcelamento']) && is_array($orcArr['parcelamento'])) {
                $linhasRaw = $orcArr['parcelamento']['linhas'] ?? [];
                $linhas = is_array($linhasRaw) ? $linhasRaw : [];
            }
        @endphp
        @if(!empty($linhas))
            @foreach($linhas as $linha)
                <span class="chip">Total de Parcelas: {{ $linha['parcelas'] ?? '-' }}</span>
                <span class="chip">Valor da Parcela: R$ {{ isset($linha['valor']) ? number_format((float)$linha['valor'], 2, ',', '.') : '-' }}</span>
                @if(isset($linha['desconto']))
                    <span class="chip">Desconto Pontualidade: R$ {{ number_format((float)$linha['desconto'], 2, ',', '.') }}</span>
                    <span class="chip">Parcela c/ Desconto: R$ {{ number_format(((float)$linha['valor']) - ((float)$linha['desconto']), 2, ',', '.') }}</span>
                @endif
            @endforeach
        @else
            <span class="chip">Sem dados de parcelamento</span>
        @endif
    </div>

    <!-- PT: Texto HTML explicativo do parcelamento | EN: Preview HTML for installment explanation -->
    <div class="content-html">
        @php
            $textoPreview = '';
            if (!empty($orcArr) && isset($orcArr['parcelamento']) && is_array($orcArr['parcelamento'])) {
                $textoPreview = $orcArr['parcelamento']['texto_preview_html'] ?? '';
            }
        @endphp
        {!! $textoPreview !!}
    </div>

    <div class="footer">Gerado em {{ $generatedAt->format('d/m/Y H:i') }}</div>
    </div>
    </div>

    <!-- PT: Páginas extras dinâmicas | EN: Dynamic extra pages -->
    @if(!empty($extra_pages))
        @foreach($extra_pages as $p)
            @php
                $pageBg = $p['background_data_uri'] ?? $p['background_url'] ?? null;
                $pageBgStyle = 'page-break-before: always; break-before: page; page-break-after: always; break-after: page; height: 297mm; width: 210mm;';
                $hasTitle = !empty($p['title']);
                $hasHtml = !empty($p['html']);
            @endphp
            <div class="page" style="{{ $pageBgStyle }}">
                @if($pageBg)
                    <img class="page-bg" src="{{ $pageBg }}" alt="" />
                @endif
                <div class="page-inner">
                @if($hasTitle)
                    <h1>{{ $p['title'] }}</h1>
                @endif
                {!! $p['html'] ?? '' !!}
                @if(!$hasTitle && !$hasHtml)
                    <div class="page-filler"></div>
                @endif
                </div>
            </div>
        @endforeach
    @endif
</body>
</html>
