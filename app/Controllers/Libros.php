<?php 
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Libro;
class Libros extends Controller{

    public function index(){

        $libro = new Libro();
        $datos['libros'] = $libro->orderBy('id', 'ASC')->findAll();

        $datos['cabecera'] = view('template/cabecera');
        $datos['pie'] = view('template/piepagina');

        return view('libros/listar', $datos);
    }

    public function crear(){

        $datos['cabecera'] = view('template/cabecera');
        $datos['pie'] = view('template/piepagina');

        return view('libros/crear', $datos);
    }

    public function guardar(){

        $libro = new Libro();

        $validacion = $this->validate([
            'nombre' => 'required|min_length[3]',
            'imagen' => [
                'uploaded[imagen]',
                'mime_in[imagen,image/jpg,image/jpeg,image/png]',
                'max_size[imagen,1024]',
            ]
        ]);

        if(!$validacion){
            $session = session(); // estas variables de sesion nos permiten enviar informacion a traves de los sitios y pues en este caso las vistas
            $session->setFlashdata('mensaje', 'Revise la informacion'); // mensaje es la variable

            return redirect()->back()->withInput();           
        }

        // $nombre = $_POST['nombre']; this is the way that i know
        //next is the new way
        //$nombre = $this->request->getVar('nombre'); //getVar is an undefined method but works xd

        if($imagen = $this->request->getFile('imagen')){
            $nuevoNombre = $imagen->getRandomName();
            $imagen->move('../public/uploads/', $nuevoNombre);

            $datos=[
                'nombre' => $this->request->getVar('nombre'),
                'imagen' => $nuevoNombre
            ];
            $libro->insert($datos);
        }

        return $this->response->redirect(site_url('/listar'));
    }

    public function borrar($id = null){

        $libro = new Libro();
        $datosLibro = $libro->where('id', $id)->first(); //to find information with an especific id 

        $ruta = ('../public/uploads/' . $datosLibro['imagen']);
        unlink($ruta); //deleting the image

        $libro->where('id', $id)->delete($id);

        return $this->response->redirect(site_url('/listar'));
    }

    public function editar($id=null){

        $datos['cabecera'] = view('template/cabecera');
        $datos['pie'] = view('template/piepagina');

        $libro = new Libro();
        $datos['libro'] = $libro->where('id', $id)->first();

        return view('libros/editar', $datos);
    }

    public function actualizar(){

        $libro = new Libro();

        $datos=[
            'nombre' => $this->request->getVar('nombre')
        ];

        $id = $this->request->getVar('id');

        $validacion = $this->validate([
            'nombre' => 'required|min_length[3]'
        ]);

        if(!$validacion){
            $session = session(); // estas variables de sesion nos permiten enviar informacion a traves de los sitios y pues en este caso las vistas
            $session->setFlashdata('mensaje', 'Revise la informacion'); // mensaje es la variable

            return redirect()->back()->withInput();           
        }

        $libro->update($id, $datos);

        $validacion = $this->validate([
            'imagen' => [
                'uploaded[imagen]',
                'mime_in[imagen,image/jpg,image/jpeg,image/png]', // if we add spaces after the coma the image update is not going to work
                'max_size[imagen,1024]',
            ]
        ]);

        if($validacion){
            if($imagen = $this->request->getFile('imagen')){

                $datosLibro = $libro->where('id', $id)->first();

                $ruta = ('../public/uploads/' . $datosLibro['imagen']);
                unlink($ruta); //deleting the image

                $nuevoNombre = $imagen->getRandomName();
                $imagen -> move('../public/uploads/', $nuevoNombre);
    
                $datos = ['imagen' => $nuevoNombre];

                $libro -> update($id, $datos);
            }
        }
        return $this->response->redirect(site_url('/listar'));
    }

}