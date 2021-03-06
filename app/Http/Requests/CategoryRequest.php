<?php

namespace App\Http\Requests;

use App\Category;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{

    protected $requried='required |';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category'=>'required||max:25',
        ];
    }

    public function messages(){
        return [
            'category.required' => 'Brand name should be provided!',
            'category.max' => 'Brand name should not be grater than 45 charats!',
        ];
    }

    public function withValidator($validator){

        $validator->after(function ($validator) {

            if ($validator->errors()->count() > 0) {
                return;
            }

            if($this->brandAvailable()){
                $validator->errors()->add('brandExsist','Brand already exsist');
            }
        });
    }

    public function brandAvailable(){
        if(Category::where('category_name',$this->category)->first()){
            return true;
        }else{
            return false;
        }
    }
}
