/**
 * RAZReports.js â€” Logic Laporan & PDF Export SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-21
 * Deskripsi: Generate laporan transaksi, arus kas, laba rugi,
 *            inventori. Export ke PDF via print window.
 */
'use strict';

const RPT = { type: 'profit_loss', dateFrom: '', dateTo: '', page: 1 };

document.addEventListener('DOMContentLoaded', () => {
  // Set default tanggal
  const now = new Date();
  RPT.dateTo = now.toISOString().split('T')[0];
  RPT.dateFrom = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
  document.getElementById('rptDateFrom').value = RPT.dateFrom;
  document.getElementById('rptDateTo').value = RPT.dateTo;

  // Report type cards
  document.querySelectorAll('.rpt-type-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.rpt-type-card').forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      RPT.type = card.dataset.report;
      RPT.page = 1;
      loadReport();
    });
  });

  // Date filter
  document.getElementById('rptDateFrom')?.addEventListener('change', (e) => { RPT.dateFrom = e.target.value; loadReport(); });
  document.getElementById('rptDateTo')?.addEventListener('change', (e) => { RPT.dateTo = e.target.value; loadReport(); });

  loadReport();
});

// ========================
// LOAD REPORT
// ========================
async function loadReport() {
  // Tampilkan section yang tepat
  document.querySelectorAll('.rpt-section').forEach(s => s.classList.remove('active'));
  document.getElementById(`rpt_${RPT.type}`)?.classList.add('active');

  switch (RPT.type) {
    case 'transactions': await loadTransactions(); break;
    case 'cashflow': await loadCashflowReport(); break;
    case 'profit_loss': await loadProfitLoss(); break;
    case 'inventory': await loadInventoryReport(); break;
  }
}

// ========================
// LAPORAN TRANSAKSI
// ========================
async function loadTransactions() {
  const params = new URLSearchParams({ action: 'transactions', date_from: RPT.dateFrom, date_to: RPT.dateTo, page: RPT.page });
  const data = await RAZ.api(`api/RAZapiReports.php?${params}`);
  if (!data.success) return;

  const s = data.data.summary;
  document.getElementById('trSumSales').textContent = RAZ.formatRupiah(s.total_sales);
  document.getElementById('trSumCount').textContent = s.count_success + ' transaksi';
  document.getElementById('trSumVoid').textContent = s.count_void + ' void';

  const tbody = document.getElementById('trBody');
  if (!data.data.transactions.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="raz-table-empty"><div class="empty-icon"><i class="ph-bold ph-receipt"></i></div><div class="empty-title">Tidak Ada Transaksi</div></div></td></tr>';
    return;
  }

  const methodLabel = { cash: 'Tunai', transfer: 'Transfer', qris: 'QRIS' };
  tbody.innerHTML = data.data.transactions.map(t => `
    <tr>
      <td><span class="raz-text-mono">${t.invoice_number}</span></td>
      <td>${t.created_at}</td>
      <td>${t.cashier_name}</td>
      <td>${methodLabel[t.payment_method] || t.payment_method}</td>
      <td class="raz-text-rupiah">${RAZ.formatRupiah(t.grand_total)}</td>
      <td><span class="raz-badge ${t.status === 'completed' ? 'success' : 'danger'}">${t.status === 'completed' ? 'Sukses' : 'Void'}</span></td>
      <td>
        <button class="raz-btn raz-btn-sm raz-btn-secondary" onclick="printOldReceipt(${t.id})" title="Cetak Struk">
          <i class="ph-bold ph-printer"></i>
        </button>
      </td>
    </tr>
  `).join('');

  document.getElementById('trPgInfo').textContent = `Hal ${data.data.page}/${data.data.pages} (${data.data.total} data)`;
}

// ========================
// CETAK STRUK LAMA
// ========================
function printOldReceipt(transId) {
  window.open('RAZreceipt.php?id=' + transId, '_blank', 'width=400,height=600');
}

// ========================
// LAPORAN ARUS KAS
// ========================
async function loadCashflowReport() {
  const params = new URLSearchParams({ action: 'cashflow', date_from: RPT.dateFrom, date_to: RPT.dateTo });
  const data = await RAZ.api(`api/RAZapiReports.php?${params}`);
  if (!data.success) return;

  document.getElementById('cfRptIncome').textContent = RAZ.formatRupiah(data.data.summary.income);
  document.getElementById('cfRptExpense').textContent = RAZ.formatRupiah(data.data.summary.expense);
  document.getElementById('cfRptNet').textContent = RAZ.formatRupiah(data.data.summary.income - data.data.summary.expense);

  const tbody = document.getElementById('cfRptBody');
  if (!data.data.cashflows.length) {
    tbody.innerHTML = '<tr><td colspan="5"><div class="raz-table-empty"><div class="empty-icon"><i class="ph-bold ph-wallet"></i></div><div class="empty-title">Tidak Ada Data</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = data.data.cashflows.map(cf => `
    <tr>
      <td>${cf.created_at}</td>
      <td><span class="cf-type-badge ${cf.type}">${cf.type === 'income' ? 'â†“ Masuk' : 'â†‘ Keluar'}</span></td>
      <td>${cf.category}</td>
      <td style="color:var(--raz-${cf.type === 'income' ? 'success' : 'danger'})">${cf.type === 'income' ? '+' : '-'}${RAZ.formatRupiah(cf.amount)}</td>
      <td>${cf.description || '-'}</td>
    </tr>
  `).join('');
}

// ========================
// LAPORAN LABA RUGI
// ========================
async function loadProfitLoss() {
  const params = new URLSearchParams({ action: 'profit_loss', date_from: RPT.dateFrom, date_to: RPT.dateTo });
  const data = await RAZ.api(`api/RAZapiReports.php?${params}`);
  if (!data.success) return;
  const r = data.data;

  let html = `
    <tr class="pl-section"><td colspan="2">PENDAPATAN</td></tr>
    <tr><td class="pl-indent">Penjualan (${r.sales_count} transaksi)</td><td class="text-right text-success">${RAZ.formatRupiah(r.sales_total)}</td></tr>
    <tr><td class="pl-indent">Pemasukan Lain</td><td class="text-right text-success">${RAZ.formatRupiah(r.other_income)}</td></tr>
    <tr class="pl-total"><td>${window.RPT_LANG ? window.RPT_LANG.lbl_total_in : (window.RPT_LANG && window.RPT_LANG.total_income) ? window.RPT_LANG.total_income : 'Total Income'}</td><td class="text-right text-success">${RAZ.formatRupiah(r.sales_total + r.other_income)}</td></tr>
    <tr class="pl-section"><td colspan="2">BEBAN</td></tr>
    <tr><td class="pl-indent">Harga Pokok Penjualan (HPP)</td><td class="text-right text-danger">${RAZ.formatRupiah(r.hpp_total)}</td></tr>`;

  r.expense_detail.forEach(exp => {
    html += `<tr><td class="pl-indent">${exp.category}</td><td class="text-right text-danger">${RAZ.formatRupiah(exp.total)}</td></tr>`;
  });

  html += `
    <tr class="pl-total"><td>Total Beban</td><td class="text-right text-danger">${RAZ.formatRupiah(r.hpp_total + r.expense_total)}</td></tr>
    <tr style="height:8px"><td colspan="2"></td></tr>
    <tr class="pl-section"><td colspan="2">RINGKASAN</td></tr>
    <tr><td class="pl-indent">Laba Kotor (Penjualan - HPP)</td><td class="text-right">${RAZ.formatRupiah(r.gross_profit)}</td></tr>
    <tr class="pl-total" style="font-size:1.1rem;"><td><strong>LABA BERSIH</strong></td><td class="text-right ${r.net_profit >= 0 ? 'text-success' : 'text-danger'}"><strong>${RAZ.formatRupiah(r.net_profit)}</strong></td></tr>`;

  document.getElementById('plBody').innerHTML = html;

  // Top items
  const topBody = document.getElementById('plTopItems');
  if (topBody && r.top_items.length) {
    topBody.innerHTML = r.top_items.map((item, i) => `
      <tr><td><span class="raz-badge primary">#${i+1}</span> ${item.item_name}</td><td>${item.total_qty}x</td><td class="raz-text-rupiah">${RAZ.formatRupiah(item.total_sales)}</td></tr>
    `).join('');
  }
}

// ========================
// LAPORAN INVENTORI
// ========================
async function loadInventoryReport() {
  const data = await RAZ.api('api/RAZapiReports.php?action=inventory_report');
  if (!data.success) return;
  const r = data.data;

  document.getElementById('invRptTotal').textContent = r.total_items + ' item';
  document.getElementById('invRptValue').textContent = RAZ.formatRupiah(r.total_stock_value);
  document.getElementById('invRptHpp').textContent = RAZ.formatRupiah(r.total_hpp_value);

  const tbody = document.getElementById('invRptBody');
  if (!r.items.length) {
    tbody.innerHTML = '<tr><td colspan="6"><div class="raz-table-empty"><div class="empty-icon"><i class="ph-bold ph-package"></i></div><div class="empty-title">Tidak Ada Barang</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = r.items.map(item => {
    const stockClass = item.stock <= 0 ? 'text-danger' : item.stock <= item.min_stock ? 'text-warning' : '';
    return `<tr>
      <td>${item.name}</td>
      <td>${item.category_name || '-'}</td>
      <td class="raz-text-rupiah">${RAZ.formatRupiah(item.hpp)}</td>
      <td class="raz-text-rupiah">${RAZ.formatRupiah(item.sell_price)}</td>
      <td class="${stockClass}">${item.stock}</td>
      <td class="raz-text-rupiah">${RAZ.formatRupiah(item.sell_price * item.stock)}</td>
    </tr>`;
  }).join('');
}

// ========================
// EXPORT PDF (via Print Window)
// ========================
async function exportPDF() {
    const params = new URLSearchParams({ action: 'pdf_data', report: RPT.type, date_from: RPT.dateFrom, date_to: RPT.dateTo });
    const data = await RAZ.api(`api/RAZapiReports.php?${params}`);
    if (!data.success) { RAZ.error('Gagal', 'Tidak dapat mengambil data laporan'); return; }

    const d = data.data;
    const store = d.store;
    const reportTitles = { transactions: (window.RPT_LANG ? window.RPT_LANG.title_trx : 'Laporan Transaksi'), cashflow: (window.RPT_LANG ? window.RPT_LANG.title_cashflow : 'Laporan Arus Kas'), profit_loss: (window.RPT_LANG ? window.RPT_LANG.title_profit_loss : 'Laporan Laba Rugi'), inventory: (window.RPT_LANG ? window.RPT_LANG.title_inventory : 'Laporan Inventori') };

    let htmlContent = '';

    if (RPT.type === 'profit_loss' && d.report) {
        const r = d.report;
        const grossProfit = r.sales_total - r.hpp_total;
        htmlContent = `
        <table class="data-table">
            <thead>
                <tr><th style="width: 70%">Keterangan</th><th style="width: 30%; text-align:right">Jumlah (Rp)</th></tr>
            </thead>
            <tbody>
                <tr class="group-header"><td colspan="2">1. ${window.RPT_LANG ? window.RPT_LANG.lbl_income : 'PENDAPATAN'}</td></tr>
                <tr><td class="pl-3">${window.RPT_LANG ? window.RPT_LANG.lbl_sales : 'Pendapatan Penjualan'} (${r.sales_count} transaksi)</td><td class="text-right text-success">${RAZ.formatRupiah(r.sales_total)}</td></tr>
                <tr><td class="pl-3">${window.RPT_LANG ? window.RPT_LANG.lbl_other : 'Pemasukan Lain-lain'}</td><td class="text-right text-success">${RAZ.formatRupiah(r.other_income)}</td></tr>
                <tr class="subtotal-row"><td>${window.RPT_LANG ? window.RPT_LANG.lbl_total_in : (window.RPT_LANG && window.RPT_LANG.total_income) ? window.RPT_LANG.total_income : 'Total Income'}</td><td class="text-right text-success">${RAZ.formatRupiah(r.sales_total + r.other_income)}</td></tr>
                
                <tr class="group-header"><td colspan="2">2. ${window.RPT_LANG ? window.RPT_LANG.lbl_cogs : 'HARGA POKOK PENJUALAN'}</td></tr>
                <tr><td class="pl-3">${window.RPT_LANG ? window.RPT_LANG.lbl_cogs_desc : 'HPP Barang Terjual & Rusak'}</td><td class="text-right text-danger">${RAZ.formatRupiah(r.hpp_total)}</td></tr>
                <tr class="subtotal-row"><td>Total HPP</td><td class="text-right text-danger">${RAZ.formatRupiah(r.hpp_total)}</td></tr>

                <tr class="gross-profit"><td>${window.RPT_LANG ? window.RPT_LANG.lbl_gross : 'LABA KOTOR'}</td><td class="text-right">${RAZ.formatRupiah(grossProfit + r.other_income)}</td></tr>

                <tr class="group-header"><td colspan="2">3. PENGELUARAN OPERASIONAL</td></tr>
                `;
        if(r.expense_detail && r.expense_detail.length > 0) {
            r.expense_detail.forEach(e => { htmlContent += `<tr><td class="pl-3">${e.category}</td><td class="text-right text-danger">${RAZ.formatRupiah(parseFloat(e.total))}</td></tr>`; });
        } else {
            htmlContent += `<tr><td class="pl-3 text-muted">Tidak ada data pengeluaran</td><td class="text-right">0</td></tr>`;
        }
        
        htmlContent += `
                <tr class="subtotal-row"><td>${window.RPT_LANG ? window.RPT_LANG.lbl_total_out : 'Total Pengeluaran'}</td><td class="text-right text-danger">${RAZ.formatRupiah(r.expense_total)}</td></tr>
                
                <tr class="net-profit"><td>${window.RPT_LANG ? window.RPT_LANG.lbl_net : 'LABA BERSIH'}</td><td class="text-right ${r.net_profit >= 0 ? 'text-success' : 'text-danger'}">${RAZ.formatRupiah(r.net_profit)}</td></tr>
            </tbody>
        </table>`;
    } 
    else if (RPT.type === 'transactions' && d.report?.transactions) {
        let totalSales = 0, successCount = 0, voidCount = 0;
        d.report.transactions.forEach(t => { 
            if(t.status === 'completed') { totalSales += parseFloat(t.grand_total); successCount++; }
            else { voidCount++; }
        });
        
        htmlContent = `
        <div class="summary-box">
            <div class="summary-item"><strong>Total Transaksi Selesai:</strong> ${successCount}</div>
            <div class="summary-item"><strong>Transaksi Void/Batal:</strong> <span class="text-danger">${voidCount}</span></div>
            <div class="summary-item"><strong>Total Omzet Penjualan:</strong> <span class="text-success">${RAZ.formatRupiah(totalSales)}</span></div>
        </div>
        <table class="data-table">
            <thead>
                <tr><th style="width:15%">Invoice</th><th style="width:15%">Tanggal</th><th style="width:15%">Kasir</th><th style="width:15%">Metode</th><th style="width:20%; text-align:right">Total (Rp)</th><th style="width:20%; text-align:center">Status</th></tr>
            </thead>
            <tbody>`;
        d.report.transactions.forEach(t => {
            const statusColor = t.status === 'completed' ? '#059669' : '#DC2626';
            const statusText = t.status === 'completed' ? 'Selesai' : 'Batal';
            htmlContent += `<tr>
                <td>${t.invoice_number}</td><td>${t.created_at.substring(0,16)}</td><td>${t.cashier_name}</td><td>${t.payment_method.toUpperCase()}</td>
                <td class="text-right"><strong>${RAZ.formatRupiah(t.grand_total)}</strong></td>
                <td style="text-align:center; font-weight:bold; color:${statusColor}">${statusText}</td>
            </tr>`;
        });
        htmlContent += `</tbody></table>`;
    } 
    else if (RPT.type === 'cashflow' && d.report?.cashflows) {
        let totalIn = 0, totalOut = 0;
        d.report.cashflows.forEach(cf => {
            if(cf.type === 'income') totalIn += parseFloat(cf.amount);
            else totalOut += parseFloat(cf.amount);
        });

        htmlContent = `
        <div class="summary-box">
            <div class="summary-item"><strong>Total Pemasukan:</strong> <span class="text-success">${RAZ.formatRupiah(totalIn)}</span></div>
            <div class="summary-item"><strong>${window.RPT_LANG ? window.RPT_LANG.lbl_total_out : 'Total Pengeluaran'}:</strong> <span class="text-danger">${RAZ.formatRupiah(totalOut)}</span></div>
            <div class="summary-item"><strong>Saldo Bersih Periode:</strong> <span>${RAZ.formatRupiah(totalIn - totalOut)}</span></div>
        </div>
        <table class="data-table">
            <thead><tr><th style="width:15%">Tanggal</th><th style="width:10%">Tipe</th><th style="width:20%">Kategori</th><th style="width:20%; text-align:right">Nominal (Rp)</th><th style="width:35%">Keterangan</th></tr></thead>
            <tbody>`;
        d.report.cashflows.forEach(cf => {
            const isIncome = cf.type === 'income';
            htmlContent += `<tr>
                <td>${cf.created_at.substring(0,16)}</td>
                <td style="font-weight:bold; color:${isIncome?'#059669':'#DC2626'}">${isIncome?'MASUK':'KELUAR'}</td>
                <td>${cf.category}</td>
                <td class="text-right" style="color:${isIncome?'#059669':'#DC2626'}"><strong>${RAZ.formatRupiah(cf.amount)}</strong></td>
                <td>${cf.description||'-'}</td>
            </tr>`;
        });
        htmlContent += `</tbody></table>`;
    } 
    else if (RPT.type === 'inventory' && d.report?.items) {
        let valHpp = 0, valSell = 0, totalItems = 0;
        let tableRows = '';
        d.report.items.forEach(item => {
            const hVal = parseFloat(item.hpp) * parseFloat(item.stock);
            const sVal = parseFloat(item.sell_price) * parseFloat(item.stock);
            valHpp += hVal;
            valSell += sVal;
            totalItems += parseFloat(item.stock);
            tableRows += `<tr>
                <td>${item.name}</td><td>${item.category_name||'-'}</td>
                <td class="text-right">${RAZ.formatRupiah(item.hpp)}</td>
                <td class="text-right">${RAZ.formatRupiah(item.sell_price)}</td>
                <td class="text-center"><strong>${item.stock}</strong></td>
                <td class="text-right">${RAZ.formatRupiah(sVal)}</td>
            </tr>`;
        });

        htmlContent = `
        <table class="data-table">
            <thead><tr><th style="width:25%">Nama Item</th><th style="width:15%">Kategori</th><th style="width:15%; text-align:right">HPP Dasar</th><th style="width:15%; text-align:right">Harga Jual</th><th style="width:10%; text-align:center">Stok</th><th style="width:20%; text-align:right">Valuasi (Jual)</th></tr></thead>
            <tbody>${tableRows}</tbody>
            <tfoot>
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <td colspan="4" class="text-right">GRAND TOTAL</td>
                    <td class="text-center">${totalItems} Pcs</td>
                    <td class="text-right text-success">${RAZ.formatRupiah(valSell)}</td>
                </tr>
            </tfoot>
        </table>
        <div class="summary-box" style="margin-top: 15px; background: transparent; border: none; padding: 0;">
            <div class="summary-item" style="color:#475569; font-size: 11px;">*Total Valuasi HPP Keseluruhan: <strong>${RAZ.formatRupiah(valHpp)}</strong></div>
            <div class="summary-item" style="color:#475569; font-size: 11px;">*Potensi Margin Kasar: <strong>${RAZ.formatRupiah(valSell - valHpp)}</strong></div>
        </div>`;
    }

    const logoHtml = store.store_logo 
        ? `<img src="${window.location.origin}/SIMAJURAZ/uploads/logos/${store.store_logo}" alt="Logo" style="max-height: 60px; margin-bottom: 10px;">` 
        : '';

    const win = window.open('', '_blank', 'width=1000,height=800');
    win.document.write(`<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>${reportTitles[RPT.type]} - ${store.store_name}</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            @page { size: A4 portrait; margin: 15mm; }
            * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { font-family: 'Inter', sans-serif; color: #1e293b; font-size: 12px; margin: 0; padding: 0; background: #fff; }
            
            /* Print Wrapper */
            .print-wrapper { max-width: 800px; margin: 0 auto; padding: 20px; }
            
            /* Header / Kop Surat */
            .kop-surat { text-align: center; border-bottom: 3px solid #1e293b; padding-bottom: 15px; margin-bottom: 25px; }
            .kop-surat h1 { font-size: 22px; color: #0f172a; margin: 0 0 5px 0; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
            .kop-surat p { font-size: 11px; color: #475569; margin: 2px 0; }
            
            /* Report Title & Meta */
            .report-title-box { text-align: center; margin-bottom: 25px; }
            .report-title { font-size: 16px; font-weight: 700; color: #1e293b; text-decoration: underline; margin-bottom: 5px; }
            .meta-info { display: flex; justify-content: space-between; font-size: 11px; color: #64748b; background: #f8fafc; padding: 8px 15px; border-radius: 6px; border: 1px solid #e2e8f0; }
            
            /* Summary Box */
            .summary-box { display: flex; justify-content: space-around; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; margin-bottom: 20px; }
            .summary-item { font-size: 12px; }
            
            /* Tables */
            .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 11px; }
            .data-table th { background-color: #1e293b; color: #ffffff; padding: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #1e293b; }
            .data-table td { padding: 8px 10px; border: 1px solid #cbd5e1; }
            .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
            .data-table tbody tr:hover { background-color: #f1f5f9; }
            
            /* Laba Rugi Specific */
            .group-header td { background-color: #e2e8f0; font-weight: 700; color: #0f172a; padding-top: 12px; padding-bottom: 12px; }
            .subtotal-row td { background-color: #f1f5f9; font-weight: 600; border-top: 2px solid #94a3b8; }
            .gross-profit td { background-color: #e0f2fe; font-weight: 700; font-size: 13px; color: #0369a1; border-top: 2px solid #0284c7; padding: 12px 10px; }
            .net-profit td { background-color: #1e293b; color: #ffffff !important; font-weight: 700; font-size: 14px; padding: 15px 10px; }
            .net-profit td.text-success { color: #4ade80 !important; }
            .net-profit td.text-danger { color: #f87171 !important; }
            
            /* Utilities */
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .pl-3 { padding-left: 25px !important; }
            .text-success { color: #059669; }
            .text-danger { color: #dc2626; }
            .text-muted { color: #94a3b8; }
            
            /* Footer & Signatures */
            .signature-section { display: flex; justify-content: flex-end; margin-top: 40px; page-break-inside: avoid; }
            .signature-box { width: 200px; text-align: center; }
            .signature-title { font-size: 11px; margin-bottom: 70px; color: #334155; }
            .signature-name { font-weight: 700; text-decoration: underline; font-size: 12px; color: #0f172a; }
            .signature-role { font-size: 10px; color: #64748b; margin-top: 3px; }
            
            .print-footer { margin-top: 30px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 10px; page-break-inside: avoid; }
            
            @media print {
                .print-wrapper { padding: 0; width: 100%; max-width: 100%; }
            }
        </style>
    </head>
    <body>
        <div class="print-wrapper">
            <div class="kop-surat">
                ${logoHtml}
                <h1>${store.store_name}</h1>
                <p>${store.store_address || 'Alamat Belum Diatur'}</p>
                <p>${store.store_phone ? 'Telp/WA: ' + store.store_phone : ''}</p>
            </div>
            
            <div class="report-title-box">
                <div class="report-title">${reportTitles[RPT.type].toUpperCase()}</div>
            </div>
            
            <div class="meta-info">
                <span><strong>${window.RPT_LANG ? window.RPT_LANG.lbl_period : 'Periode'}:</strong> ${d.period.from} s/d ${d.period.to}</span>
                <span><strong>${window.RPT_LANG ? window.RPT_LANG.lbl_print_date : 'Dicetak pada'}:</strong> ${d.generated_at}</span>
            </div>
            
            ${htmlContent}
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-title">${window.RPT_LANG ? window.RPT_LANG.lbl_sign : 'Mengetahui'},</div>
                    <div class="signature-name">${d.generated_by}</div>
                    <div class="signature-role">${window.RPT_LANG ? window.RPT_LANG.lbl_owner : 'Manajemen / Owner'}</div>
                </div>
            </div>
            
            <div class="print-footer">
                Digenerate secara otomatis oleh sistem POS & Akuntansi <strong>SIMAJURAZ</strong> v${typeof RAZ_VERSION !== 'undefined' ? RAZ_VERSION : '1.0.0'} &copy; RAZ Creative Studio
            </div>
        </div>
        <script>
            window.onload = function() { setTimeout(()=>{window.print();},800); }
        </script>
    </body>
    </html>`);
    win.document.close();
}


