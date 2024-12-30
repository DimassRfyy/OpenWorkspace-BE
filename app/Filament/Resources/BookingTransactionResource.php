<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;
use App\Models\BookingTransaction;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Twilio\Rest\Client;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Booking Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('booking_trx_id')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('phone_number')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('total_amount')
                ->required()
                ->numeric()
                ->prefix('IDR'),

                Forms\Components\TextInput::make('duration')
                ->required()
                ->numeric()
                ->prefix('Days'),

                Forms\Components\DatePicker::make('started_at')
                ->required(),

                Forms\Components\DatePicker::make('ended_at')
                ->required(),

                Forms\Components\Select::make('is_paid')
                ->options([
                    true => 'Paid',
                    false => 'Unpaid',
                ])
                ->required(),

                Forms\Components\Select::make('office_space_id')
                ->relationship('officeSpace', 'name')
                ->searchable()
                ->preload()
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('booking_trx_id')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('officeSpace.name'),

                Tables\Columns\TextColumn::make('started_at')
                    ->date(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->label('Sudah Bayar?'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->action(function (BookingTransaction $record) {
                        $record->is_paid = true;
                        $record->save();

                        //Trigger the custom notification
                        Notification::make()
                            ->title('Booking Approved')
                            ->success()
                            ->body('The booking has been successfuly approved.')
                            ->send();

        //                     $sid = getenv("TWILIO_ACCOUNT_SID");
        //                     $token = getenv("TWILIO_AUTH_TOKEN");
        //                     $twilio = new Client($sid, $token);

        //                     //create the message with line breaks
        //                     $messageBody = "Hi {$record->name},  pemesanan Anda dengan kode TRX ID: {$record->booking_trx_id} sudah terbayar penuh.\n\n";
        //                     $messageBody .= "Silahkan datang kepada lokasi kantor {$record->officeSpace->name} untuk mulai menggunakan ruangan kerja tersebut.\n\n";
        //                     $messageBody .= "Jika Anda memiliki pertanyaan lebih lanjut, silahkan hubungi kami.";

        //                     $message = $twilio->messages
        // ->create("whatsapp:+{$record->phone_number}",
        //     array(
        //         "from" => "whatsapp:+14155238886",
        //         "body" => $messageBody
        //     ));


                    })
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (BookingTransaction $record) => !$record->is_paid),
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
            'index' => Pages\ListBookingTransactions::route('/'),
            'create' => Pages\CreateBookingTransaction::route('/create'),
            'edit' => Pages\EditBookingTransaction::route('/{record}/edit'),
        ];
    }
}
