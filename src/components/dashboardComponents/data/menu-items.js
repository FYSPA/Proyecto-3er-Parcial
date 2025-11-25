// Data de datos de Sidebar.astro de DashboardPage.astro
export const menuItems = [
  {
    titulo: "Inicio", //Se especifica el titulo
    descripcion: "Página principal", //Se especifica la descripcion
    tipoIcono: "component", //Se especifica el tipo si es 'component' o 'image'
    iconKey: "house", //Se especifica el nombre de la varibale si es component
    iconSrc: null, //Se coloca null si es component ya que no hay imagen
    descripcion_icono: "Icono casa", //Se especifica la descripcion del icono
    texto_etiqueta: "Inicio", //Se especifica el texto de la etiqueta
    ruta_dinamica: "dashboardpage/Dashboard" //Se especifica la ruta dinamica
  },
  {
    titulo: "Juego", //Se especifica el titulo
    descripcion: "Panel de usuario", //Se especifica la descripcion
    tipoIcono: "image", //Se especifica el tipo si es 'component' o 'image'
    iconKey: null, //Se coloca null si es image ya que no hay component
    iconSrc: "/gamesImg/CrucibleLamentIcon.jpg", //Se especifica la ruta de la imagen
    descripcion_icono: "Icono dashboard", //Se especifica la descripcion del icono
    texto_etiqueta: "Juego", //Se especifica el texto de la etiqueta
    ruta_dinamica: "dashboardpage/TheCrucibleLaments" //Se especifica la ruta dinamica
  },
  {
    titulo: "Hola", //Se especifica el titulo
    descripcion: "Panel de usuario", //Se especifica la descripcion
    tipoIcono: "image", //Se especifica el tipo si es 'component' o 'image'
    iconKey: null, //Se coloca null si es image ya que no hay component
    iconSrc: "/icons/minecraft.svg", //Se especifica la ruta de la imagen
    descripcion_icono: "Icono dashboard", //Se especifica la descripcion del icono
    texto_etiqueta: "Hola", //Se especifica el texto de la etiqueta
    ruta_dinamica: "dashboardpage/" //Se especifica la ruta dinamica
  },
];
