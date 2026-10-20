/**
 * DataTables Bahasa Indonesia Localization & Defaults
 * LOFBI - KSOP Kelas I Banten
 */
window.DataTablesIndonesian = {
    emptyTable: "Tidak ada data yang tersedia pada tabel ini",
    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
    infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
    infoThousands: ".",
    lengthMenu: "Tampilkan _MENU_ entri",
    loadingRecords: "Sedang memuat data...",
    processing: "Sedang memproses...",
    search: "Cari:",
    searchPlaceholder: "Ketik kata kunci pencarian...",
    zeroRecords: "Tidak ditemukan data yang sesuai",
    paginate: {
        first: "Pertama",
        last: "Terakhir",
        next: "Berikutnya",
        previous: "Sebelumnya"
    },
    aria: {
        sortAscending: ": aktifkan untuk mengurutkan kolom ke atas",
        sortDescending: ": aktifkan untuk mengurutkan kolom ke bawah"
    }
};

// Set global default config when jQuery & DataTables exist
if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.dataTable) {
    jQuery.extend(true, jQuery.fn.dataTable.defaults, {
        language: window.DataTablesIndonesian,
        pageLength: 10,
        responsive: true,
        autoWidth: false,
        dom: "<'row align-items-center mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-end'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 text-md-end'p>>"
    });
}
