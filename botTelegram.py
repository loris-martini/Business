import mysql.connector
from datetime import datetime, timedelta
from telegram import Update, InlineKeyboardButton, InlineKeyboardMarkup
from telegram.ext import Updater, CommandHandler, CallbackQueryHandler, CallbackContext, MessageHandler, Filters

# Configurazione del database
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'my_salone'
}

# Configurazione del bot
TOKEN = "7663284466:AAGI0xCgbpEat8soj6lZ3yHiWT4lnlFKQcI"
ADMIN_ID = 1268972115

# Connessione al database
def get_db_connection():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as err:
        print(f"Errore di connessione al database: {err}")
        return None

# Funzioni per il database
def aggiungi_prenotazione(user_id, nome, cognome, telefono, data_ora, servizio_id, barbiere_mail):
    conn = get_db_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        
        # Verifica se l'utente esiste già
        cursor.execute("SELECT mail FROM utenti WHERE numero_telefono = %s", (telefono,))
        utente = cursor.fetchone()
        
        if utente:
            mail_cliente = utente[0]
        else:
            # Crea un nuovo utente cliente
            mail_cliente = f"{nome.lower()}.{cognome.lower()}@clientemail.com"
            cursor.execute(
                "INSERT INTO utenti (mail, nome, cognome, password, numero_telefono, ruolo) "
                "VALUES (%s, %s, %s, %s, %s, 'CLIENTE')",
                (mail_cliente, nome, cognome, 'default_password', telefono)
            )
        
        # Trova il turno del barbiere per quella data/ora
        data = data_ora.date()
        ora = data_ora.time()
        
        cursor.execute(
            "SELECT id_turno FROM turni_barbieri "
            "WHERE fk_barbiere = %s AND giorno = %s AND ora_inizio <= %s AND ora_fine >= %s",
            (barbiere_mail, data.strftime("%A"), ora, ora)
        turno = cursor.fetchone()
        
        if not turno:
            raise Exception("Nessun turno trovato per il barbiere in questo orario")
        
        # Aggiungi l'appuntamento
        cursor.execute(
            "INSERT INTO appuntamenti (fk_cliente, fk_turno, fk_servizio, data_app, ora_inizio, stato) "
            "VALUES (%s, %s, %s, %s, %s, 'CONFERMATO')",
            (mail_cliente, turno[0], servizio_id, data, ora)
        )
        
        conn.commit()
        return True
    except Exception as e:
        print(f"Errore durante l'aggiunta della prenotazione: {e}")
        conn.rollback()
        return False
    finally:
        conn.close()

def elimina_prenotazione(appuntamento_id):
    conn = get_db_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        cursor.execute(
            "UPDATE appuntamenti SET stato = 'CANCELLATO' WHERE id_appuntamento = %s",
            (appuntamento_id,)
        )
        conn.commit()
        return cursor.rowcount > 0
    except Exception as e:
        print(f"Errore durante l'eliminazione della prenotazione: {e}")
        conn.rollback()
        return False
    finally:
        conn.close()

def modifica_prenotazione(appuntamento_id, nuova_data_ora, nuovo_barbiere_mail=None):
    conn = get_db_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        
        # Trova il turno del barbiere per la nuova data/ora
        data = nuova_data_ora.date()
        ora = nuova_data_ora.time()
        
        if nuovo_barbiere_mail:
            barbiere_mail = nuovo_barbiere_mail
        else:
            # Mantieni lo stesso barbiere se non specificato
            cursor.execute(
                "SELECT fk_barbiere FROM turni_barbieri tb "
                "JOIN appuntamenti a ON tb.id_turno = a.fk_turno "
                "WHERE a.id_appuntamento = %s", (appuntamento_id,))
            barbiere_mail = cursor.fetchone()[0]
        
        cursor.execute(
            "SELECT id_turno FROM turni_barbieri "
            "WHERE fk_barbiere = %s AND giorno = %s AND ora_inizio <= %s AND ora_fine >= %s",
            (barbiere_mail, data.strftime("%A"), ora, ora)
        turno = cursor.fetchone()
        
        if not turno:
            raise Exception("Nessun turno trovato per il barbiere in questo orario")
        
        # Aggiorna l'appuntamento
        cursor.execute(
            "UPDATE appuntamenti SET fk_turno = %s, data_app = %s, ora_inizio = %s "
            "WHERE id_appuntamento = %s",
            (turno[0], data, ora, appuntamento_id)
        )
        
        conn.commit()
        return True
    except Exception as e:
        print(f"Errore durante la modifica della prenotazione: {e}")
        conn.rollback()
        return False
    finally:
        conn.close()

def ottieni_prenotazioni_utente(telefono):
    conn = get_db_connection()
    if not conn:
        return []
    
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute(
            "SELECT a.id_appuntamento, u.nome, u.cognome, a.data_app, a.ora_inizio, s.nome as servizio, "
            "ub.nome as barbiere_nome, ub.cognome as barbiere_cognome, a.stato "
            "FROM appuntamenti a "
            "JOIN utenti u ON a.fk_cliente = u.mail "
            "JOIN servizi s ON a.fk_servizio = s.id_servizio "
            "JOIN turni_barbieri tb ON a.fk_turno = tb.id_turno "
            "JOIN utenti ub ON tb.fk_barbiere = ub.mail "
            "WHERE u.numero_telefono = %s AND a.stato != 'CANCELLATO'",
            (telefono,))
        return cursor.fetchall()
    except Exception as e:
        print(f"Errore durante il recupero delle prenotazioni: {e}")
        return []
    finally:
        conn.close()

def ottieni_tutte_prenotazioni():
    conn = get_db_connection()
    if not conn:
        return []
    
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute(
            "SELECT a.id_appuntamento, u.nome, u.cognome, u.numero_telefono, "
            "a.data_app, a.ora_inizio, s.nome as servizio, "
            "ub.nome as barbiere_nome, ub.cognome as barbiere_cognome, a.stato "
            "FROM appuntamenti a "
            "JOIN utenti u ON a.fk_cliente = u.mail "
            "JOIN servizi s ON a.fk_servizio = s.id_servizio "
            "JOIN turni_barbieri tb ON a.fk_turno = tb.id_turno "
            "JOIN utenti ub ON tb.fk_barbiere = ub.mail "
            "WHERE a.stato != 'CANCELLATO'")
        return cursor.fetchall()
    except Exception as e:
        print(f"Errore durante il recupero di tutte le prenotazioni: {e}")
        return []
    finally:
        conn.close()

def verifica_disponibilita(barbiere_mail, data_ora, durata_servizio):
    conn = get_db_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        
        data = data_ora.date()
        ora = data_ora.time()
        giorno_settimana = data.strftime("%A")
        
        # Verifica che il barbiere lavori in quel giorno e orario
        cursor.execute(
            "SELECT id_turno FROM turni_barbieri "
            "WHERE fk_barbiere = %s AND giorno = %s AND ora_inizio <= %s AND ora_fine >= %s",
            (barbiere_mail, giorno_settimana, ora, ora))
        turno = cursor.fetchone()
        
        if not turno:
            return False
        
        # Calcola l'orario di fine servizio
        ora_fine_servizio = (datetime.combine(data, ora) + timedelta(minutes=durata_servizio)).time()
        
        # Verifica sovrapposizioni con altri appuntamenti
        cursor.execute(
            "SELECT 1 FROM appuntamenti a "
            "JOIN turni_barbieri tb ON a.fk_turno = tb.id_turno "
            "WHERE tb.fk_barbiere = %s AND a.data_app = %s AND a.stato = 'CONFERMATO' "
            "AND ((a.ora_inizio <= %s AND ADDTIME(a.ora_inizio, SEC_TO_TIME(s.durata*60)) > %s) "
            "OR (a.ora_inizio < %s AND ADDTIME(a.ora_inizio, SEC_TO_TIME(s.durata*60)) >= %s)) "
            "JOIN servizi s ON a.fk_servizio = s.id_servizio",
            (barbiere_mail, data, ora, ora, ora_fine_servizio, ora_fine_servizio))
        
        return cursor.fetchone() is None
    except Exception as e:
        print(f"Errore durante la verifica della disponibilità: {e}")
        return False
    finally:
        conn.close()

def ottieni_servizi_disponibili():
    conn = get_db_connection()
    if not conn:
        return []
    
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id_servizio, nome, durata, prezzo FROM servizi")
        return cursor.fetchall()
    except Exception as e:
        print(f"Errore durante il recupero dei servizi: {e}")
        return []
    finally:
        conn.close()

def ottieni_barbieri_disponibili(data_ora):
    conn = get_db_connection()
    if not conn:
        return []
    
    try:
        cursor = conn.cursor(dictionary=True)
        giorno_settimana = data_ora.strftime("%A")
        ora = data_ora.time()
        
        cursor.execute(
            "SELECT DISTINCT u.mail, u.nome, u.cognome "
            "FROM utenti u "
            "JOIN turni_barbieri tb ON u.mail = tb.fk_barbiere "
            "WHERE u.ruolo = 'BARBIERE' AND tb.giorno = %s AND tb.ora_inizio <= %s AND tb.ora_fine >= %s",
            (giorno_settimana, ora, ora))
        return cursor.fetchall()
    except Exception as as e:
        print(f"Errore durante il recupero dei barbieri disponibili: {e}")
        return []
    finally:
        conn.close()

# Comandi per l'utente
def start(update: Update, context: CallbackContext):
    update.message.reply_text(
        "👋 Benvenuto nel **PrenotaBarbiereBot**!\n"
        "📌 Usa /prenota per fissare un appuntamento.\n"
        "❌ Usa /cancella per annullare una prenotazione.\n"
        "🔄 Usa /modifica per cambiare l'orario.\n"
        "📋 Usa /mieprenotazioni per vedere i tuoi appuntamenti."
    )

def prenota(update: Update, context: CallbackContext):
    update.message.reply_text("✏️ Inserisci il tuo nome:")
    context.user_data["step"] = "nome"

def gestisci_nome(update: Update, context: CallbackContext):
    nome = update.message.text
    context.user_data["nome"] = nome
    update.message.reply_text("✏️ Inserisci il tuo cognome:")
    context.user_data["step"] = "cognome"

def gestisci_cognome(update: Update, context: CallbackContext):
    cognome = update.message.text
    context.user_data["cognome"] = cognome
    update.message.reply_text("📱 Inserisci il tuo numero di telefono (10 cifre):")
    context.user_data["step"] = "telefono"

def gestisci_telefono(update: Update, context: CallbackContext):
    telefono = update.message.text
    if not telefono.isdigit() or len(telefono) != 10:
        update.message.reply_text("❌ Numero di telefono non valido. Inserisci 10 cifre numeriche.")
        return
    
    context.user_data["telefono"] = telefono
    
    # Mostra i servizi disponibili
    servizi = ottieni_servizi_disponibili()
    if not servizi:
        update.message.reply_text("❌ Nessun servizio disponibile al momento.")
        return
    
    keyboard = []
    for servizio in servizi:
        keyboard.append([InlineKeyboardButton(
            f"{servizio['nome']} ({servizio['durata']} min) - €{servizio['prezzo']}",
            callback_data=f"servizio_{servizio['id_servizio']}"
        )])
    
    reply_markup = InlineKeyboardMarkup(keyboard)
    update.message.reply_text("🔧 Scegli il servizio:", reply_markup=reply_markup)
    context.user_data["step"] = "servizio"

def gestisci_servizio(update: Update, context: CallbackContext):
    query = update.callback_query
    query.answer()
    
    servizio_id = int(query.data.split('_')[1])
    context.user_data["servizio_id"] = servizio_id
    
    query.edit_message_text("📅 Inserisci la data e l'ora desiderata (formato: GG/MM/AAAA HH:MM):")
    context.user_data["step"] = "data_ora"

def gestisci_data_ora(update: Update, context: CallbackContext):
    try:
        data_ora_str = update.message.text
        data_ora = datetime.strptime(data_ora_str, "%d/%m/%Y %H:%M")
        
        # Ottieni la durata del servizio selezionato
        servizi = ottieni_servizi_disponibili()
        durata = next((s['durata'] for s in servizi if s['id_servizio'] == context.user_data["servizio_id"]), 30)
        
        # Trova barbieri disponibili
        barbieri = ottieni_barbieri_disponibili(data_ora)
        if not barbieri:
            update.message.reply_text("❌ Nessun barbiere disponibile in questo orario. Riprova.")
            return
        
        # Verifica disponibilità per ogni barbiere
        barbieri_disponibili = []
        for barbiere in barbieri:
            if verifica_disponibilita(barbiere['mail'], data_ora, durata):
                barbieri_disponibili.append(barbiere)
        
        if not barbieri_disponibili:
            update.message.reply_text("❌ Nessun barbiere disponibile in questo orario. Riprova.")
            return
        
        # Mostra i barbieri disponibili
        keyboard = []
        for barbiere in barbieri_disponibili:
            keyboard.append([InlineKeyboardButton(
                f"{barbiere['nome']} {barbiere['cognome']}",
                callback_data=f"barbiere_{barbiere['mail']}"
            )])
        
        reply_markup = InlineKeyboardMarkup(keyboard)
        update.message.reply_text("💈 Scegli il barbiere:", reply_markup=reply_markup)
        context.user_data["step"] = "barbiere"
        context.user_data["data_ora"] = data_ora
    except ValueError:
        update.message.reply_text("⚠️ Formato errato. Usa DD/MM/AAAA HH:MM")

def conferma_prenotazione(update: Update, context: CallbackContext):
    query = update.callback_query
    query.answer()
    
    barbiere_mail = query.data.split('_')[1]
    nome = context.user_data["nome"]
    cognome = context.user_data["cognome"]
    telefono = context.user_data["telefono"]
    data_ora = context.user_data["data_ora"]
    servizio_id = context.user_data["servizio_id"]
    
    if aggiungi_prenotazione(update.effective_user.id, nome, cognome, telefono, data_ora, servizio_id, barbiere_mail):
        query.edit_message_text("✅ Prenotazione confermata!\n"
                              f"📅 Data: {data_ora.strftime('%d/%m/%Y %H:%M')}\n"
                              f"👤 Barbiere: {barbiere_mail.split('@')[0]}\n"
                              "Grazie per aver scelto il nostro servizio!")
    else:
        query.edit_message_text("❌ Si è verificato un errore durante la prenotazione. Riprova più tardi.")

def cancella_prenotazione(update: Update, context: CallbackContext):
    telefono = update.message.from_user.id  # Qui dovresti avere un modo per ottenere il telefono dell'utente
    prenotazioni = ottieni_prenotazioni_utente(telefono)
    
    if not prenotazioni:
        update.message.reply_text("📭 Non hai nessuna prenotazione attiva.")
        return
    
    keyboard = []
    for prenotazione in prenotazioni:
        keyboard.append([InlineKeyboardButton(
            f"{prenotazione['data_app']} {prenotazione['ora_inizio']} - {prenotazione['servizio']}",
            callback_data=f"cancella_{prenotazione['id_appuntamento']}"
        )])
    
    reply_markup = InlineKeyboardMarkup(keyboard)
    update.message.reply_text("❌ Seleziona la prenotazione da cancellare:", reply_markup=reply_markup)

def esegui_cancellazione(update: Update, context: CallbackContext):
    query = update.callback_query
    query.answer()
    
    appuntamento_id = int(query.data.split('_')[1])
    if elimina_prenotazione(appuntamento_id):
        query.edit_message_text("✅ Prenotazione cancellata con successo!")
    else:
        query.edit_message_text("❌ Impossibile cancellare la prenotazione. Riprova più tardi.")

def modifica_prenotazione(update: Update, context: CallbackContext):
    telefono = update.message.from_user.id  # Qui dovresti avere un modo per ottenere il telefono dell'utente
    prenotazioni = ottieni_prenotazioni_utente(telefono)
    
    if not prenotazioni:
        update.message.reply_text("📭 Non hai nessuna prenotazione attiva.")
        return
    
    keyboard = []
    for prenotazione in prenotazioni:
        keyboard.append([InlineKeyboardButton(
            f"{prenotazione['data_app']} {prenotazione['ora_inizio']} - {prenotazione['servizio']}",
            callback_data=f"modifica_{prenotazione['id_appuntamento']}"
        )])
    
    reply_markup = InlineKeyboardMarkup(keyboard)
    update.message.reply_text("🔄 Seleziona la prenotazione da modificare:", reply_markup=reply_markup)

def avvia_modifica_prenotazione(update: Update, context: CallbackContext):
    query = update.callback_query
    query.answer()
    
    appuntamento_id = int(query.data.split('_')[1])
    context.user_data["appuntamento_id"] = appuntamento_id
    query.edit_message_text("📅 Inserisci la nuova data e ora (formato: GG/MM/AAAA HH:MM):")
    context.user_data["step"] = "modifica_data_ora"

def completa_modifica_prenotazione(update: Update, context: CallbackContext):
    try:
        nuova_data_ora_str = update.message.text
        nuova_data_ora = datetime.strptime(nuova_data_ora_str, "%d/%m/%Y %H:%M")
        appuntamento_id = context.user_data["appuntamento_id"]
        
        if modifica_prenotazione(appuntamento_id, nuova_data_ora):
            update.message.reply_text("✅ Prenotazione modificata con successo!")
        else:
            update.message.reply_text("❌ Impossibile modificare la prenotazione. Riprova più tardi.")
    except ValueError:
        update.message.reply_text("⚠️ Formato errato. Usa DD/MM/AAAA HH:MM")

def mie_prenotazioni(update: Update, context: CallbackContext):
    telefono = update.message.from_user.id  # Qui dovresti avere un modo per ottenere il telefono dell'utente
    prenotazioni = ottieni_prenotazioni_utente(telefono)
    
    if not prenotazioni:
        update.message.reply_text("📭 Non hai nessuna prenotazione attiva.")
        return
    
    message = "📋 Le tue prenotazioni:\n\n"
    for pren in prenotazioni:
        message += (
            f"🔹 ID: {pren['id_appuntamento']}\n"
            f"📅 Data: {pren['data_app']} {pren['ora_inizio']}\n"
            f"💈 Barbiere: {pren['barbiere_nome']} {pren['barbiere_cognome']}\n"
            f"🔧 Servizio: {pren['servizio']}\n"
            f"📌 Stato: {pren['stato']}\n\n"
        )
    
    update.message.reply_text(message)

# Comandi Admin
def admin_prenotazioni(update: Update, context: CallbackContext):
    if update.message.from_user.id != ADMIN_ID:
        update.message.reply_text("🚫 Solo l'admin può usare questo comando.")
        return
    
    prenotazioni = ottieni_tutte_prenotazioni()
    if not prenotazioni:
        update.message.reply_text("📭 Nessuna prenotazione trovata.")
        return
    
    message = "📋 Tutte le prenotazioni:\n\n"
    for pren in prenotazioni:
        message += (
            f"🔹 ID: {pren['id_appuntamento']}\n"
            f"👤 Cliente: {pren['nome']} {pren['cognome']} ({pren['numero_telefono']})\n"
            f"📅 Data: {pren['data_app']} {pren['ora_inizio']}\n"
            f"💈 Barbiere: {pren['barbiere_nome']} {pren['barbiere_cognome']}\n"
            f"🔧 Servizio: {pren['servizio']}\n"
            f"📌 Stato: {pren['stato']}\n\n"
        )
    
    update.message.reply_text(message)

# Handler per i messaggi
def handle_message(update: Update, context: CallbackContext):
    if context.user_data.get("step") == "nome":
        gestisci_nome(update, context)
    elif context.user_data.get("step") == "cognome":
        gestisci_cognome(update, context)
    elif context.user_data.get("step") == "telefono":
        gestisci_telefono(update, context)
    elif context.user_data.get("step") == "data_ora":
        gestisci_data_ora(update, context)
    elif context.user_data.get("step") == "modifica_data_ora":
        completa_modifica_prenotazione(update, context)

# Handler per le callback
def handle_callback(update: Update, context: CallbackContext):
    query = update.callback_query
    data = query.data
    
    if data.startswith("servizio_"):
        gestisci_servizio(update, context)
    elif data.startswith("barbiere_"):
        conferma_prenotazione(update, context)
    elif data.startswith("cancella_"):
        esegui_cancellazione(update, context)
    elif data.startswith("modifica_"):
        avvia_modifica_prenotazione(update, context)

# Main
def main():
    updater = Updater(TOKEN)
    dispatcher = updater.dispatcher

    # Comandi utente
    dispatcher.add_handler(CommandHandler("start", start))
    dispatcher.add_handler(CommandHandler("prenota", prenota))
    dispatcher.add_handler(CommandHandler("cancella", cancella_prenotazione))
    dispatcher.add_handler(CommandHandler("modifica", modifica_prenotazione))
    dispatcher.add_handler(CommandHandler("mieprenotazioni", mie_prenotazioni))

    # Comandi admin
    dispatcher.add_handler(CommandHandler("admin", admin_prenotazioni))

    # Gestione messaggi
    dispatcher.add_handler(MessageHandler(Filters.text & ~Filters.command, handle_message))
    
    # Gestione callback
    dispatcher.add_handler(CallbackQueryHandler(handle_callback))

    updater.start_polling()
    updater.idle()

if __name__ == "__main__":
    main()