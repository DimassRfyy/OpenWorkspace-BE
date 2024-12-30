<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeSpaceResource\Pages;
use App\Filament\Resources\OfficeSpaceResource\RelationManagers;
use App\Models\OfficeSpace;
use Filament\Forms\Components\Wizard;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OfficeSpaceResource extends Resource
{
    protected static ?string $model = OfficeSpace::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Places Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Main Information')
                        ->icon('heroicon-m-information-circle')
                        ->completedIcon('heroicon-m-check')
                        ->description('Information of the office space.')
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                            Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),

                            Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->disk('public')
                            ->directory('office-spaces')
                            ->required(),

                            Forms\Components\TextArea::make('about')
                            ->required()
                            ->rows(10)
                            ->cols(20),

                            Forms\Components\Select::make('city_id')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                            Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR'),

                            Forms\Components\TextInput::make('duration')
                            ->required()
                            ->numeric()
                            ->prefix('Days'),

                            Forms\Components\Select::make('is_open')
                            ->options([
                                true => 'Open',
                                false => 'Not Open',
                            ])
                            ->required(),

                            Forms\Components\Select::make('is_full_booked')
                            ->options([
                                true => 'Not Available',
                                false => 'Available',
                            ])
                            ->required(),
                            
                            Forms\Components\Select::make('is_popular')
                            ->options([
                                true => 'Popular',
                                false => 'Not Popular',
                            ])
                            ->required(),
                        ]),
                    Wizard\Step::make('Benefits')
                        ->icon('heroicon-m-arrow-trending-up')
                        ->completedIcon('heroicon-m-check')
                        ->description('Benefits of the office space.')
                        ->schema([
                            Forms\Components\Repeater::make('benefits')
                            ->grid(2)
                            ->defaultItems(4)
                            ->relationship('benefits')
                            ->schema([
                            Forms\Components\TextInput::make('name')
                            ->required(),
                        ]),
                        ]),
                    Wizard\Step::make('Photos')
                        ->icon('heroicon-m-photo')
                        ->completedIcon('heroicon-m-check')
                        ->description('Photos of the office space.')
                        ->schema([
                            Forms\Components\Repeater::make('photos')
                            ->grid(2)
                            ->defaultItems(4)
                            ->relationship('photos')
                            ->schema([
                            Forms\Components\FileUpload::make('photo')
                            ->image()
                            ->disk('public')
                            ->directory('office-spaces-photos')
                            ->required(),
                        ]),
                        ]),
                ])
                ->columnSpan('full')
                ->skippable()
                ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('name')
                ->searchable(),

                Tables\Columns\ImageColumn::make('thumbnail'),

                Tables\Columns\TextColumn::make('city.name'),

                Tables\Columns\IconColumn::make('is_full_booked')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-s-x-circle')
                    ->falseIcon('heroicon-s-check-circle')
                    ->label('Available'),
                
                Tables\Columns\IconColumn::make('is_popular')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->label('Popular'),
            ])
            ->filters([
                //
                SelectFilter::make('city_id')
                ->label('City')
                ->relationship('city', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfficeSpaces::route('/'),
            'create' => Pages\CreateOfficeSpace::route('/create'),
            'edit' => Pages\EditOfficeSpace::route('/{record}/edit'),
        ];
    }
}
