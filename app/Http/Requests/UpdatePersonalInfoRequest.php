<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'favorite_song' => 'nullable|string|max:255',
            'favorite_movie' => 'nullable|string|max:255',
            'favorite_book' => 'nullable|string|max:255',
            'favorite_dish' => 'nullable|string|max:255',
            'best_friend_name' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'favorite_song.string' => 'Любимая песня должна быть строкой',
            'favorite_song.max' => 'Название любимой песни не должно превышать 255 символов',
            'favorite_movie.string' => 'Любимый фильм должен быть строкой',
            'favorite_movie.max' => 'Название любимого фильма не должно превышать 255 символов',
            'favorite_book.string' => 'Любимая книга должна быть строкой',
            'favorite_book.max' => 'Название любимой книги не должно превышать 255 символов',
            'favorite_dish.string' => 'Любимое блюдо должно быть строкой',
            'favorite_dish.max' => 'Название любимого блюда не должно превышать 255 символов',
            'best_friend_name.string' => 'Имя лучшего друга должно быть строкой',
            'best_friend_name.max' => 'Имя лучшего друга не должно превышать 255 символов',
        ];
    }
}
