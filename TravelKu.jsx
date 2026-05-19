import { useState, useMemo } from "react";
import {
  Plus, Search, Filter, X, Edit2, Trash2, ChevronDown,
  CheckCircle, XCircle, Clock, MapPin, Users, DollarSign,
  Calendar, Phone, AlertCircle, Loader, ArrowRight,
  FileText, RefreshCw, BarChart3
} from "lucide-react";

/* ─── Constants ─── */
const PACKAGES = [
  "Bali 4D3N", "Lombok 3D2N", "Yogyakarta 2D1N",
  "Raja Ampat 5D4N", "Labuan Bajo 4D3N", "Komodo 3D2N",
  "Manado 4D3N", "Bunaken 3D2N", "Wakatobi 4D3N",
];

const STATUSES = ["Menunggu", "Dikonfirmasi", "Selesai", "Dibatalkan"];

const STATUS_TRANSITIONS = {
  "Menunggu":    ["Dikonfirmasi", "Dibatalkan"],
  "Dikonfirmasi":["Selesai", "Dibatalkan"],
  "Selesai":     [],
  "Dibatalkan":  [],
};

const STATUS_STYLE = {
  "Menunggu":    { pill:"bg-amber-100 text-amber-800 border border-amber-200", dot:"bg-amber-400" },
  "Dikonfirmasi":{ pill:"bg-sky-100 text-sky-800 border border-sky-200",     dot:"bg-sky-500"   },
  "Selesai":     { pill:"bg-emerald-100 text-emerald-800 border border-emerald-200", dot:"bg-emerald-500" },
  "Dibatalkan":  { pill:"bg-red-100 text-red-700 border border-red-200",     dot:"bg-red-400"   },
};

const NEW_STATUS_BTN = {
  "Dikonfirmasi":"bg-sky-50 hover:bg-sky-100 text-sky-700 border-sky-200",
  "Selesai":     "bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200",
  "Dibatalkan":  "bg-red-50 hover:bg-red-100 text-red-700 border-red-200",
};

const EMPTY_FORM = {
  name:"", contact:"", package:"", departureDate:"",
  participants:1, pricePerPerson:0, notes:""
};

const SEEDS = [
  { id:"s1", name:"Budi Santoso",   contact:"081234567890",   package:"Bali 4D3N",        departureDate:"2026-06-15", participants:4, pricePerPerson:2500000, status:"Dikonfirmasi", notes:"Minta kamar non-smoking", createdAt: Date.now()-864e5*3 },
  { id:"s2", name:"Siti Rahayu",    contact:"siti@email.com", package:"Lombok 3D2N",       departureDate:"2026-07-10", participants:2, pricePerPerson:1800000, status:"Menunggu",    notes:"",                         createdAt: Date.now()-864e5*1 },
  { id:"s3", name:"Ahmad Fauzi",    contact:"085678901234",   package:"Yogyakarta 2D1N",   departureDate:"2026-05-25", participants:6, pricePerPerson:950000,  status:"Selesai",     notes:"Group tour mahasiswa",      createdAt: Date.now()-864e5*5 },
  { id:"s4", name:"Dewi Kusuma",    contact:"dewi@gmail.com", package:"Raja Ampat 5D4N",   departureDate:"2026-08-02", participants:3, pricePerPerson:5800000, status:"Menunggu",    notes:"Honeymoon package",         createdAt: Date.now()-864e5*2 },
  { id:"s5", name:"Hendra Wijaya",  contact:"081999888777",   package:"Labuan Bajo 4D3N",  departureDate:"2026-09-14", participants:8, pricePerPerson:3200000, status:"Dikonfirmasi",notes:"Grup kantor",               createdAt: Date.now()-864e5*4 },
  { id:"s6", name:"Rina Hastuti",   contact:"rina.h@mail.id", package:"Komodo 3D2N",       departureDate:"2026-06-01", participants:2, pricePerPerson:2900000, status:"Dibatalkan",  notes:"Pembatalan karena sakit",   createdAt: Date.now()-864e5*6 },
];

/* ─── Helpers ─── */
const fmtCurrency = (n) =>
  new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(n);

const fmtDate = (d) =>
  d ? new Date(d).toLocaleDateString("id-ID",{day:"numeric",month:"short",year:"numeric"}) : "-";

const genId = () => Math.random().toString(36).slice(2, 11);

/* ─── Server-side Validation (Anthropic API) ─── */
async function validateServer(data) {
  const today = new Date().toISOString().split("T")[0];
  const prompt = `Kamu adalah validator server-side untuk sistem pemesanan travel. Validasi data ini dan kembalikan HANYA JSON (tanpa markdown/backtick).

Format respons: {"valid":boolean,"errors":{"field":"pesan error"}}

Data:
- name: "${data.name}"
- contact: "${data.contact}"  
- package: "${data.package}"
- departureDate: "${data.departureDate}"
- participants: ${data.participants}
- pricePerPerson: ${data.pricePerPerson}

Aturan (hari ini: ${today}):
1. name → wajib isi, min 3 karakter, hanya huruf & spasi
2. contact → wajib isi; valid jika nomor HP Indonesia (awalan 08/+62, 10-15 digit) ATAU email valid
3. package → wajib dipilih (tidak boleh kosong)
4. departureDate → wajib isi, tidak boleh di masa lalu
5. participants → bilangan bulat, min 1, max 100
6. pricePerPerson → angka positif, min 10000

Jika semua valid, {"valid":true,"errors":{}}. Jika ada yang salah, {"valid":false,"errors":{"nama_field":"pesan bahasa Indonesia"}}. HANYA JSON.`;

  try {
    const res = await fetch("https://api.anthropic.com/v1/messages", {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify({
        model:"claude-sonnet-4-20250514",
        max_tokens:400,
        messages:[{role:"user",content:prompt}]
      })
    });
    const json = await res.json();
    const text = json.content?.[0]?.text || "";
    const clean = text.replace(/```json|```/g,"").trim();
    return JSON.parse(clean);
  } catch {
    return {valid:false, errors:{general:"Validasi server gagal. Periksa koneksi."}};
  }
}

/* ══════════════════════════════════════════════════
   MAIN COMPONENT
══════════════════════════════════════════════════ */
export default function TravelKu() {
  const [bookings,    setBookings]    = useState(SEEDS);
  const [filters,     setFilters]     = useState({status:"",package:"",dateFrom:"",dateTo:""});
  const [search,      setSearch]      = useState("");
  const [showFilters, setShowFilters] = useState(false);
  const [modal,       setModal]       = useState({open:false, mode:"add", booking:null});
  const [form,        setForm]        = useState(EMPTY_FORM);
  const [errors,      setErrors]      = useState({});
  const [validating,  setValidating]  = useState(false);
  const [statusModal, setStatusModal] = useState({open:false, booking:null});
  const [delConfirm,  setDelConfirm]  = useState(null);
  const [toast,       setToast]       = useState(null);

  /* ── Derived list ── */
  const filtered = useMemo(() => {
    let list = [...bookings].sort((a,b) => b.createdAt - a.createdAt);
    if (filters.status)   list = list.filter(b => b.status   === filters.status);
    if (filters.package)  list = list.filter(b => b.package  === filters.package);
    if (filters.dateFrom) list = list.filter(b => b.departureDate >= filters.dateFrom);
    if (filters.dateTo)   list = list.filter(b => b.departureDate <= filters.dateTo);
    if (search) {
      const q = search.toLowerCase();
      list = list.filter(b =>
        b.name.toLowerCase().includes(q) ||
        b.contact.toLowerCase().includes(q) ||
        b.package.toLowerCase().includes(q)
      );
    }
    return list;
  }, [bookings, filters, search]);

  const summary = useMemo(() => {
    const revenue = filtered
      .filter(b => b.status==="Dikonfirmasi" || b.status==="Selesai")
      .reduce((s,b) => s + b.participants * b.pricePerPerson, 0);
    const byStatus = Object.fromEntries(STATUSES.map(s => [s, filtered.filter(b=>b.status===s).length]));
    return {total:filtered.length, revenue, byStatus};
  }, [filtered]);

  /* ── Toast helper ── */
  const showToast = (msg, type="success") => {
    setToast({msg,type});
    setTimeout(() => setToast(null), 3200);
  };

  /* ── Modal helpers ── */
  const openAdd = () => {
    setForm(EMPTY_FORM); setErrors({});
    setModal({open:true, mode:"add", booking:null});
  };
  const openEdit = (b) => {
    setForm({...b}); setErrors({});
    setModal({open:true, mode:"edit", booking:b});
  };
  const closeModal = () => setModal({open:false, mode:"add", booking:null});

  /* ── Submit (with server validation) ── */
  const handleSubmit = async () => {
    setValidating(true); setErrors({});
    const result = await validateServer(form);
    if (!result.valid) {
      setErrors(result.errors || {});
      setValidating(false);
      return;
    }
    if (modal.mode === "add") {
      setBookings(prev => [{
        ...form, id:genId(), status:"Menunggu",
        participants:Number(form.participants),
        pricePerPerson:Number(form.pricePerPerson),
        createdAt:Date.now()
      }, ...prev]);
      showToast("Pemesanan baru berhasil ditambahkan!");
    } else {
      setBookings(prev => prev.map(b =>
        b.id===modal.booking.id ? {
          ...b, ...form,
          participants:Number(form.participants),
          pricePerPerson:Number(form.pricePerPerson)
        } : b
      ));
      showToast("Pemesanan berhasil diperbarui!");
    }
    setValidating(false);
    closeModal();
  };

  /* ── Status change ── */
  const handleStatus = (booking, newStatus) => {
    setBookings(prev => prev.map(b =>
      b.id===booking.id ? {...b, status:newStatus} : b
    ));
    setStatusModal({open:false, booking:null});
    showToast(`Status diubah ke "${newStatus}"`);
  };

  /* ── Delete ── */
  const handleDelete = (id) => {
    setBookings(prev => prev.filter(b => b.id!==id));
    setDelConfirm(null);
    showToast("Pemesanan dihapus.", "info");
  };

  const hasFilter = Object.values(filters).some(v=>v) || search;
  const resetAll  = () => { setFilters({status:"",package:"",dateFrom:"",dateTo:""}); setSearch(""); };

  /* ══ RENDER ══ */
  return (
    <div className="min-h-screen bg-slate-100 font-sans">

      {/* ── Toast ── */}
      {toast && (
        <div className={`fixed top-4 right-4 z-[9999] flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium transition-all
          ${toast.type==="success" ? "bg-emerald-600 text-white" : "bg-slate-700 text-white"}`}>
          {toast.type==="success" ? <CheckCircle className="w-4 h-4"/> : <AlertCircle className="w-4 h-4"/>}
          {toast.msg}
        </div>
      )}

      {/* ── Sidebar + Content wrapper ── */}
      <div className="flex min-h-screen">

        {/* Sidebar */}
        <aside className="w-56 bg-teal-900 flex-shrink-0 hidden md:flex flex-col">
          {/* Brand */}
          <div className="px-5 py-6 border-b border-teal-800">
            <div className="flex items-center gap-2.5">
              <div className="w-8 h-8 bg-teal-400 rounded-lg flex items-center justify-center">
                <MapPin className="w-4 h-4 text-teal-900"/>
              </div>
              <div>
                <p className="text-white font-extrabold text-base leading-none tracking-tight">TravelKu</p>
                <p className="text-teal-400 text-[10px] leading-none mt-0.5">Sistem Agen Perjalanan</p>
              </div>
            </div>
          </div>

          {/* Nav */}
          <nav className="flex-1 px-3 py-4 space-y-1">
            {[
              {icon:<BarChart3 className="w-4 h-4"/>, label:"Manajemen Booking", active:true},
              {icon:<Users className="w-4 h-4"/>,     label:"Data Pelanggan",   active:false},
              {icon:<MapPin className="w-4 h-4"/>,    label:"Paket Wisata",     active:false},
              {icon:<FileText className="w-4 h-4"/>,  label:"Laporan",          active:false},
            ].map(item => (
              <button key={item.label}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors text-left
                  ${item.active
                    ? "bg-teal-700 text-white"
                    : "text-teal-300 hover:bg-teal-800 hover:text-white opacity-60"}`}>
                {item.icon} {item.label}
              </button>
            ))}
          </nav>

          {/* Footer */}
          <div className="px-4 py-4 border-t border-teal-800">
            <div className="flex items-center gap-2">
              <div className="w-7 h-7 rounded-full bg-teal-600 flex items-center justify-center text-xs text-white font-bold">ST</div>
              <div>
                <p className="text-white text-xs font-medium">Staff Agen</p>
                <p className="text-teal-400 text-[10px]">Internal System</p>
              </div>
            </div>
          </div>
        </aside>

        {/* Main */}
        <div className="flex-1 flex flex-col min-w-0">

          {/* Top Bar */}
          <header className="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div>
              <h1 className="font-bold text-slate-800 text-lg">Manajemen Pemesanan</h1>
              <p className="text-slate-400 text-xs">Kelola semua pemesanan paket wisata pelanggan</p>
            </div>
            <button onClick={openAdd}
              className="flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white px-4 py-2.5 rounded-xl font-semibold text-sm shadow-sm transition-colors">
              <Plus className="w-4 h-4"/> Tambah Pemesanan
            </button>
          </header>

          {/* Content Area */}
          <main className="flex-1 p-6 space-y-5 overflow-auto">

            {/* ── Summary Cards ── */}
            <div className="grid grid-cols-2 xl:grid-cols-4 gap-4">
              {[
                {
                  label:"Total Pemesanan",
                  value:summary.total,
                  sub: hasFilter ? "hasil filter aktif" : "semua pemesanan",
                  icon:<BarChart3 className="w-5 h-5"/>,
                  color:"text-teal-600 bg-teal-50",
                },
                {
                  label:"Estimasi Pendapatan",
                  value:fmtCurrency(summary.revenue),
                  sub:"Dikonfirmasi + Selesai",
                  icon:<DollarSign className="w-5 h-5"/>,
                  color:"text-emerald-600 bg-emerald-50",
                },
                {
                  label:"Menunggu Konfirmasi",
                  value:summary.byStatus["Menunggu"],
                  sub:"perlu tindak lanjut",
                  icon:<Clock className="w-5 h-5"/>,
                  color:"text-amber-600 bg-amber-50",
                },
                {
                  label:"Sudah Dikonfirmasi",
                  value:summary.byStatus["Dikonfirmasi"],
                  sub:"siap berangkat",
                  icon:<CheckCircle className="w-5 h-5"/>,
                  color:"text-sky-600 bg-sky-50",
                },
              ].map((c,i) => (
                <div key={i} className="bg-white rounded-2xl border border-slate-200 p-4 flex items-start gap-3 shadow-sm">
                  <div className={`${c.color} w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0`}>
                    {c.icon}
                  </div>
                  <div className="min-w-0">
                    <p className="text-xs text-slate-500 font-medium truncate">{c.label}</p>
                    <p className="text-xl font-extrabold text-slate-800 leading-tight truncate">{c.value}</p>
                    <p className="text-[11px] text-slate-400 truncate">{c.sub}</p>
                  </div>
                </div>
              ))}
            </div>

            {/* ── Filter Bar ── */}
            <div className="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3">
              <div className="flex items-center gap-3 flex-wrap">
                {/* Search */}
                <div className="flex-1 min-w-52 relative">
                  <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                  <input
                    className="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all"
                    placeholder="Cari nama, kontak, atau paket wisata..."
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                  />
                </div>
                {/* Filter toggle */}
                <button
                  onClick={() => setShowFilters(s => !s)}
                  className={`flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium border transition-colors
                    ${showFilters ? "bg-teal-700 text-white border-teal-700" : "border-slate-200 text-slate-600 hover:bg-slate-50"}`}>
                  <Filter className="w-4 h-4"/>
                  Filter Lanjutan
                  {hasFilter && !showFilters && (
                    <span className="w-5 h-5 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold">
                      {Object.values(filters).filter(v=>v).length + (search?1:0)}
                    </span>
                  )}
                </button>
                {hasFilter && (
                  <button onClick={resetAll} className="flex items-center gap-1.5 text-sm text-red-500 hover:text-red-700 font-medium">
                    <RefreshCw className="w-3.5 h-3.5"/> Reset
                  </button>
                )}
              </div>

              {showFilters && (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-slate-100">
                  {[
                    {
                      label:"Status",
                      el: (
                        <select className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500"
                          value={filters.status} onChange={e => setFilters(f=>({...f,status:e.target.value}))}>
                          <option value="">Semua Status</option>
                          {STATUSES.map(s=><option key={s}>{s}</option>)}
                        </select>
                      )
                    },
                    {
                      label:"Paket Wisata",
                      el: (
                        <select className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500"
                          value={filters.package} onChange={e => setFilters(f=>({...f,package:e.target.value}))}>
                          <option value="">Semua Paket</option>
                          {PACKAGES.map(p=><option key={p}>{p}</option>)}
                        </select>
                      )
                    },
                    {
                      label:"Berangkat Dari",
                      el: (
                        <input type="date"
                          className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500"
                          value={filters.dateFrom} onChange={e => setFilters(f=>({...f,dateFrom:e.target.value}))}/>
                      )
                    },
                    {
                      label:"Berangkat Sampai",
                      el: (
                        <input type="date"
                          className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500"
                          value={filters.dateTo} onChange={e => setFilters(f=>({...f,dateTo:e.target.value}))}/>
                      )
                    },
                  ].map(({label,el}) => (
                    <div key={label}>
                      <label className="text-xs text-slate-500 font-medium mb-1.5 block">{label}</label>
                      {el}
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* ── Booking Table ── */}
            <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
              {/* Table Header */}
              <div className="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h2 className="font-bold text-slate-700 text-sm">
                  Daftar Pemesanan
                  <span className="ml-2 bg-teal-100 text-teal-700 text-xs font-semibold px-2 py-0.5 rounded-full">{filtered.length}</span>
                </h2>
                <p className="text-xs text-slate-400">Data terbaru ditampilkan paling atas</p>
              </div>

              {/* Table */}
              <div className="overflow-x-auto">
                <table className="w-full text-sm min-w-[780px]">
                  <thead>
                    <tr className="bg-slate-50 border-b border-slate-100">
                      {["Pemesan & Kontak","Paket Wisata","Tgl Berangkat","Peserta","Harga Total","Status","Aksi"].map(h => (
                        <th key={h} className="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-widest whitespace-nowrap">
                          {h}
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    {filtered.length === 0 ? (
                      <tr>
                        <td colSpan={7} className="px-4 py-16 text-center">
                          <MapPin className="w-10 h-10 mx-auto mb-3 text-slate-200"/>
                          <p className="text-slate-400 font-medium">Tidak ada pemesanan ditemukan</p>
                          <p className="text-slate-300 text-xs mt-1">Coba ubah filter atau tambah pemesanan baru</p>
                        </td>
                      </tr>
                    ) : filtered.map((b, idx) => (
                      <tr key={b.id}
                        className={`border-b border-slate-50 hover:bg-teal-50/40 transition-colors ${idx%2===0?"bg-white":"bg-slate-50/30"}`}>
                        {/* Pemesan */}
                        <td className="px-4 py-3.5">
                          <p className="font-semibold text-slate-800">{b.name}</p>
                          <p className="text-slate-400 text-xs flex items-center gap-1 mt-0.5">
                            <Phone className="w-3 h-3"/> {b.contact}
                          </p>
                          {b.notes && (
                            <p className="text-slate-400 text-xs mt-1 italic truncate max-w-[160px]">
                              "{b.notes}"
                            </p>
                          )}
                        </td>
                        {/* Paket */}
                        <td className="px-4 py-3.5">
                          <span className="inline-flex items-center gap-1.5 bg-teal-50 text-teal-700 font-semibold text-xs px-2.5 py-1 rounded-lg border border-teal-100">
                            <MapPin className="w-3 h-3"/> {b.package}
                          </span>
                        </td>
                        {/* Tanggal */}
                        <td className="px-4 py-3.5">
                          <p className="text-slate-700 font-medium">{fmtDate(b.departureDate)}</p>
                          <p className="text-slate-400 text-xs">{b.departureDate}</p>
                        </td>
                        {/* Peserta */}
                        <td className="px-4 py-3.5">
                          <span className="flex items-center gap-1 text-slate-600 font-medium">
                            <Users className="w-3.5 h-3.5 text-slate-400"/> {b.participants} orang
                          </span>
                        </td>
                        {/* Harga */}
                        <td className="px-4 py-3.5">
                          <p className="font-bold text-slate-800 text-sm">{fmtCurrency(b.participants * b.pricePerPerson)}</p>
                          <p className="text-slate-400 text-[11px] mt-0.5">{fmtCurrency(b.pricePerPerson)}/orang</p>
                        </td>
                        {/* Status */}
                        <td className="px-4 py-3.5">
                          <button
                            onClick={() => STATUS_TRANSITIONS[b.status].length > 0 && setStatusModal({open:true, booking:b})}
                            className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border transition-opacity
                              ${STATUS_STYLE[b.status].pill}
                              ${STATUS_TRANSITIONS[b.status].length > 0 ? "cursor-pointer hover:opacity-70" : "cursor-default"}`}>
                            <span className={`w-1.5 h-1.5 rounded-full ${STATUS_STYLE[b.status].dot}`}/>
                            {b.status}
                            {STATUS_TRANSITIONS[b.status].length > 0 && <ChevronDown className="w-3 h-3"/>}
                          </button>
                          {STATUS_TRANSITIONS[b.status].length === 0 && (
                            <p className="text-[10px] text-slate-400 mt-1">Status final</p>
                          )}
                        </td>
                        {/* Aksi */}
                        <td className="px-4 py-3.5">
                          <div className="flex items-center gap-1">
                            <button onClick={() => openEdit(b)}
                              className="p-2 rounded-lg text-slate-400 hover:text-teal-600 hover:bg-teal-50 transition-colors" title="Edit">
                              <Edit2 className="w-4 h-4"/>
                            </button>
                            <button onClick={() => setDelConfirm(b)}
                              className="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                              <Trash2 className="w-4 h-4"/>
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {/* Table Footer */}
              {filtered.length > 0 && (
                <div className="px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs text-slate-500">
                  <span>Menampilkan {filtered.length} dari {bookings.length} pemesanan</span>
                  <span>
                    Pendapatan terfilter:{" "}
                    <span className="font-bold text-emerald-600">{fmtCurrency(summary.revenue)}</span>
                  </span>
                </div>
              )}
            </div>
          </main>
        </div>
      </div>

      {/* ══════════════════════════════════════
          MODAL: Tambah / Edit Pemesanan
      ══════════════════════════════════════ */}
      {modal.open && (
        <div className="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" style={{position:"fixed"}}>
          <div className="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh]">
            {/* Header */}
            <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
              <div className="flex items-center gap-3">
                <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${modal.mode==="add" ? "bg-teal-100" : "bg-amber-100"}`}>
                  {modal.mode==="add" ? <Plus className="w-4 h-4 text-teal-700"/> : <Edit2 className="w-4 h-4 text-amber-700"/>}
                </div>
                <div>
                  <h2 className="font-bold text-slate-800">{modal.mode==="add" ? "Tambah Pemesanan Baru" : "Edit Pemesanan"}</h2>
                  <p className="text-xs text-slate-400">{modal.mode==="add" ? "Status awal: Menunggu" : `ID: ${modal.booking?.id}`}</p>
                </div>
              </div>
              <button onClick={closeModal} className="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">
                <X className="w-5 h-5"/>
              </button>
            </div>

            {/* Body */}
            <div className="p-6 overflow-y-auto flex-1 space-y-4">
              {/* General error */}
              {errors.general && (
                <div className="bg-red-50 border border-red-200 rounded-xl p-3 flex items-center gap-2 text-red-700 text-sm">
                  <AlertCircle className="w-4 h-4 flex-shrink-0"/> {errors.general}
                </div>
              )}

              {/* Validating banner */}
              {validating && (
                <div className="bg-teal-50 border border-teal-200 rounded-xl p-3 flex items-center gap-2 text-teal-700 text-sm">
                  <Loader className="w-4 h-4 animate-spin flex-shrink-0"/>
                  Memvalidasi data di server…
                </div>
              )}

              {/* Fields */}
              <Field label="Nama Pemesan *" error={errors.name}>
                <input type="text" placeholder="Nama lengkap pemesan"
                  className={Input(errors.name)}
                  value={form.name}
                  onChange={e => setForm(f=>({...f,name:e.target.value}))}/>
              </Field>

              <Field label="Kontak (HP / Email) *" error={errors.contact}>
                <input type="text" placeholder="08xxxxxxxxxx atau nama@email.com"
                  className={Input(errors.contact)}
                  value={form.contact}
                  onChange={e => setForm(f=>({...f,contact:e.target.value}))}/>
              </Field>

              <Field label="Paket Wisata *" error={errors.package}>
                <select className={Input(errors.package)}
                  value={form.package}
                  onChange={e => setForm(f=>({...f,package:e.target.value}))}>
                  <option value="">Pilih paket wisata…</option>
                  {PACKAGES.map(p=><option key={p}>{p}</option>)}
                </select>
              </Field>

              <Field label="Tanggal Keberangkatan *" error={errors.departureDate}>
                <input type="date" className={Input(errors.departureDate)}
                  value={form.departureDate}
                  onChange={e => setForm(f=>({...f,departureDate:e.target.value}))}/>
              </Field>

              <div className="grid grid-cols-2 gap-4">
                <Field label="Jumlah Peserta *" error={errors.participants}>
                  <input type="number" min="1" max="100" placeholder="Min. 1"
                    className={Input(errors.participants)}
                    value={form.participants}
                    onChange={e => setForm(f=>({...f,participants:e.target.value}))}/>
                </Field>
                <Field label="Harga per Orang (Rp) *" error={errors.pricePerPerson}>
                  <input type="number" min="0" placeholder="mis. 2500000"
                    className={Input(errors.pricePerPerson)}
                    value={form.pricePerPerson}
                    onChange={e => setForm(f=>({...f,pricePerPerson:e.target.value}))}/>
                </Field>
              </div>

              {/* Total preview */}
              {Number(form.participants)>0 && Number(form.pricePerPerson)>0 && (
                <div className="bg-teal-50 border border-teal-100 rounded-xl p-3.5 flex items-center justify-between">
                  <span className="text-sm text-teal-600 font-medium">Estimasi Total</span>
                  <span className="text-base font-extrabold text-teal-800">
                    {fmtCurrency(Number(form.participants)*Number(form.pricePerPerson))}
                  </span>
                </div>
              )}

              <Field label="Catatan (opsional)">
                <textarea rows={3} placeholder="Permintaan khusus, alergi, kebutuhan lain…"
                  className="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all resize-none"
                  value={form.notes}
                  onChange={e => setForm(f=>({...f,notes:e.target.value}))}/>
              </Field>
            </div>

            {/* Footer */}
            <div className="px-6 pb-6 pt-2 flex gap-3 flex-shrink-0">
              <button onClick={closeModal}
                className="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Batal
              </button>
              <button onClick={handleSubmit} disabled={validating}
                className="flex-1 bg-teal-700 hover:bg-teal-800 disabled:opacity-60 text-white rounded-xl py-2.5 text-sm font-bold flex items-center justify-center gap-2 transition-colors shadow-sm">
                {validating
                  ? <><Loader className="w-4 h-4 animate-spin"/> Memvalidasi…</>
                  : modal.mode==="add" ? "Simpan Pemesanan" : "Simpan Perubahan"}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ══════════════════════════════════════
          MODAL: Ubah Status
      ══════════════════════════════════════ */}
      {statusModal.open && statusModal.booking && (
        <div className="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" style={{position:"fixed"}}>
          <div className="bg-white rounded-2xl w-full max-w-xs shadow-2xl p-6">
            <h2 className="font-bold text-slate-800 mb-0.5">Ubah Status Pemesanan</h2>
            <p className="text-sm text-slate-500 mb-1">{statusModal.booking.name}</p>
            <p className="text-xs text-slate-400 mb-5">{statusModal.booking.package} · {fmtDate(statusModal.booking.departureDate)}</p>

            {/* Current */}
            <div className="flex items-center gap-2 mb-5 pb-4 border-b border-slate-100">
              <span className="text-xs text-slate-500">Status saat ini:</span>
              <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border ${STATUS_STYLE[statusModal.booking.status].pill}`}>
                <span className={`w-1.5 h-1.5 rounded-full ${STATUS_STYLE[statusModal.booking.status].dot}`}/>
                {statusModal.booking.status}
              </span>
            </div>

            {/* Next options */}
            <div className="space-y-2">
              {STATUS_TRANSITIONS[statusModal.booking.status].map(ns => (
                <button key={ns}
                  onClick={() => handleStatus(statusModal.booking, ns)}
                  className={`w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold border transition-colors ${NEW_STATUS_BTN[ns]}`}>
                  <span className="flex items-center gap-2">
                    <ArrowRight className="w-4 h-4"/> Ubah ke "{ns}"
                  </span>
                  <span className={`w-2 h-2 rounded-full ${STATUS_STYLE[ns].dot}`}/>
                </button>
              ))}
            </div>

            <button onClick={() => setStatusModal({open:false, booking:null})}
              className="w-full mt-3 py-2.5 rounded-xl text-sm text-slate-500 hover:bg-slate-50 border border-slate-200 transition-colors">
              Batal
            </button>
          </div>
        </div>
      )}

      {/* ══════════════════════════════════════
          MODAL: Konfirmasi Hapus
      ══════════════════════════════════════ */}
      {delConfirm && (
        <div className="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" style={{position:"fixed"}}>
          <div className="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center">
            <div className="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <Trash2 className="w-7 h-7 text-red-600"/>
            </div>
            <h2 className="font-bold text-slate-800 text-lg mb-1">Hapus Pemesanan?</h2>
            <p className="text-sm text-slate-500 mb-1">
              Pemesanan atas nama <span className="font-semibold text-slate-700">{delConfirm.name}</span>
            </p>
            <p className="text-xs text-slate-400 mb-6">{delConfirm.package} · {fmtDate(delConfirm.departureDate)}</p>
            <div className="flex gap-3">
              <button onClick={() => setDelConfirm(null)}
                className="flex-1 border border-slate-200 rounded-xl py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Batal
              </button>
              <button onClick={() => handleDelete(delConfirm.id)}
                className="flex-1 bg-red-600 hover:bg-red-700 text-white rounded-xl py-2.5 text-sm font-bold transition-colors">
                Ya, Hapus
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

/* ─── Helper Components ─── */
function Field({label, error, children}) {
  return (
    <div>
      <label className="text-sm font-semibold text-slate-700 mb-1.5 block">{label}</label>
      {children}
      {error && (
        <p className="text-red-500 text-xs mt-1 flex items-center gap-1">
          <AlertCircle className="w-3 h-3"/> {error}
        </p>
      )}
    </div>
  );
}

function Input(hasError) {
  return `w-full border rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 transition-all
    ${hasError
      ? "border-red-300 bg-red-50 focus:ring-red-400"
      : "border-slate-200 focus:ring-teal-500"}`;
}
