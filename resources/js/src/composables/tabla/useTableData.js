import { ref, computed, onMounted, onBeforeUnmount, unref } from "vue";

export default function useTableData(items, {
  defaultOrderBy = "id",
  searchFields = []
} = {}) {
  const query = ref("");
  const orderDirection = ref("asc"); 
  const orderBy = ref(defaultOrderBy);

  const pagina = ref(1);
  const itemsPorPagina = ref(10);

  function calcularItemsPorPantalla() {
    const altura = window.innerHeight;
    if (altura < 600) itemsPorPagina.value = 4;
    else if (altura < 800) itemsPorPagina.value = 9;
    else if (altura < 1000) itemsPorPagina.value = 12;
    else itemsPorPagina.value = 14;
  }

  onMounted(() => {
    calcularItemsPorPantalla();
    window.addEventListener("resize", calcularItemsPorPantalla);
  });

  onBeforeUnmount(() => {
    window.removeEventListener("resize", calcularItemsPorPantalla);
  });

  function filtrar({ query: texto = "", orderDirection: orden, orderBy: campo } = {}) {
    if (texto !== undefined) query.value = texto.toString().toLowerCase();
    if (orden) orderDirection.value = orden;
    if (campo) orderBy.value = campo;
    pagina.value = 1;
  }

  const lista = computed(() => {
    const raw = unref(items) || [];
    return raw.map(item => {
      const safeItem = { ...item };
      searchFields.forEach(campo => {
        if (safeItem[campo] == null) safeItem[campo] = "";
      });
      if (safeItem[orderBy.value] == null) safeItem[orderBy.value] = "";
      return safeItem;
    });
  });

  const filtrados = computed(() => {
    if (!query.value || !searchFields.length) return lista.value;
    return lista.value.filter(item =>
      searchFields.some(campo =>
        item[campo]?.toString().toLowerCase().includes(query.value)
      )
    );
  });

  const ordenados = computed(() => {
    const array = [...filtrados.value];
    return array.sort((a, b) => {
      // Tomamos todo el apellido completo (compuesto)
      const aVal = (a[orderBy.value] ?? "").toString().toLowerCase();
      const bVal = (b[orderBy.value] ?? "").toString().toLowerCase();
      return orderDirection.value === "asc"
        ? aVal.localeCompare(bVal, undefined, { numeric: true })
        : bVal.localeCompare(aVal, undefined, { numeric: true });
    });
  });

  const paginados = computed(() => {
    const start = (pagina.value - 1) * itemsPorPagina.value;
    return ordenados.value.slice(start, start + itemsPorPagina.value);
  });

  const totalPaginas = computed(() =>
    Math.ceil(ordenados.value.length / itemsPorPagina.value)
  );

  return {
    query,
    orderBy,
    orderDirection,
    pagina,
    itemsPorPagina,
    filtrados,
    ordenados,
    paginados,
    totalPaginas,
    filtrar
  };
}
