<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['input_invalid_format']               = "Le format d'entrée n'est pas valide.";
$lang['no_record_found']                    = 'Aucun Enregistrement Trouvé.';
$lang['feedback_saved']                     = 'Les commentaires ont bien été enregistrés.';
$lang['mobile_already_attached_to_other']   = 'Désolé, ce numéro de mobile est déjà associé à un autre compte social. ';
$lang['email_already_attached_to_other']    = 'Désolé, cet e-mail est déjà joint à un autre compte social..';
$lang['your_account_deactivated']           = "Votre compte a été désactivé. Veuillez contacter l'administrateur du site.";
$lang['otp_send_success']                   = "OTP envoyé sur votre téléphone n'est valable que pendant 30 secondes.";
$lang['resend_otp_send_success']            = 'OTP a été envoyé avec succès.';
$lang['otp_multiple_request']               = 'Vous ne pouvez pas envoyer plusieurs demandes en même temps.';
$lang['email_otp_send_success']             = 'OTP envoyé sur votre adresse e-mail est valable pendant 30 secondes seulement.';
$lang['number_update_otp_message']          = "Votre OTP est # OTP #. Veuillez saisir ceci pour vérifier votre mobile. Merci d'avoir choisi " . SITE_TITLE;
$lang['phone_verified_success']             = 'Votre numéro de téléphone a bien été vérifié.';
$lang['email_verified_success']             = 'Votre adresse e-mail a été vérifiée avec succès.';
$lang['phone_verified_failed']              = 'Veuillez entrer un code valide.';
$lang['email_verified_failed']              = 'Veuillez entrer un code valide.';
$lang['no_account_found']                   = "Aucun compte n'a été trouvé en utilisant ce détail. Veuillez réessayer ou vous inscrire.";
$lang['social_required']                    = 'Au moins un détail social requis.';
$lang['logout_successfully']                = 'Déconnexion réussie.';
$lang['download_apk_link']                  = "Merci pour ton intérêt. Veuillez vérifier les SMS sur votre téléphone et télécharger l'APK à partir du lien spécifié dans le message.";
$lang['apk_link_msg']                       = "Rejoignez-nous sur ".SITE_TITLE." et participez à des concours gratuits et GAGNEZ de l'argent réel! Téléchargez l'application Android: %s (copiez dans le navigateur si le téléchargement ne démarre pas)";
$lang['invalid_activation_key']             = "Clé d'activation non valide.";
$lang['account_already_verified']           = 'Votre compte a déjà été vérifié.';
$lang['invalid_link']                       = 'Ce lien a expiré.';
$lang['account_confirm']                    = 'Votre compte a été confirmé.';
$lang['not_a_valid_user']                   = 'Pas un utilisateur valide.';
$lang['welcome_email_subject']              = 'Confirmez votre existence, humaine!';
$lang['signup_email_subject']               = SITE_TITLE.': Vérification du compte OTP';
$lang['email_confirm']                      = 'Veuillez visiter %s pour confirmer votre adresse e-mail.';
$lang['access_token_required']              = "Veuillez saisir un jeton d'accès.";
$lang['invalid_facebook_id']                = 'Identifiant Facebook non valide.';
$lang['invalid_google_id']                  = 'ID Google non valide.';
$lang['user_name_required']                 = "Nom d'utilisateur est nécessaire.";
$lang['invalid_device_type']                = 'Type de périphérique non valide.';
$lang['device_id_required']                 = "Veuillez saisir l'identifiant de l'appareil mobile.";
$lang['device_id_updated']                  = "ID de l'appareil mis à jour.";
$lang['password_required']                  = 'Mot de passe requis.';
$lang['phone_no_required']                  = 'Le numéro de téléphone est requis.';
$lang['invalid_email_or_password']          = 'email ou mot de passe invalide.';
$lang["should_be_greater_than_or_equal"]    = 'Veuillez saisir un montant supérieur ou égal à';
$lang["numeric_only"]                       = 'Le champ %s doit être numérique';
$lang["greater_than_zero"]                  = 'Le champ %s doit être supérieur à zéro.';
$lang['login_success']                      = 'Connectez-vous avec succès.';
$lang['email_not_exist']                    = "Le compte avec cet e-mail n'existe pas.";
$lang['forgot_password_email_subject']      = ' Demande de changement de mot de passe!';
$lang['forgot_pass_mail_sent']              = 'Veuillez visiter %s pour réinitialiser votre mot de passe.';
$lang['forgot_passwork_email_message']      = 'Veuillez suivre le lien ci-dessous pour réinitialiser votre mot de passe.';
$lang['invalid_password_link']              = 'Clé de réinitialisation du mot de passe invalide.';
$lang['valid_code']                         = 'Code valide';
$lang['not_a_valid_code']                   = 'Veuillez entrer un code valide.';
$lang['password_reset_success']             = 'Réinitialisation du mot de passe réussie.';
$lang['enter_valid_ref_code']               = 'Veuillez saisir un code de référence valide.';
$lang['enter_valid_user_setting']           = 'Veuillez saisir un paramètre utilisateur valide.';
$lang['signup_success']                     = 'Inscription réussie';
$lang['referral_code_applied']              = 'Code de parrainage appliqué avec succès.';
$lang['password_update_success']            = 'Mot de passe mis à jour avec succès.';
$lang['username_update_success']            = "Le nom d'utilisateur a bien été mis à jour.";
$lang['email_update_success']               = 'E-mail mis à jour avec succès.';
$lang['password']                           = 'Mot de passe';
$lang['confirmed_password']                 = 'Confirmez le mot de passe';
$lang["old_password"]                       = 'ancien mot de passe';
$lang['reset_password_done']                = 'Le mot de passe a été changé avec succès.';
$lang['file_not_found']                     = "Vous n'avez pas sélectionné de fichier à télécharger.";
$lang['invalid_image_size']                 = "Veuillez télécharger un fichier image d'une taille maximale de 4 Mo";
$lang['invalid_image_ext']                  = "Veuillez télécharger l'image avec l'extension %s uniquement";
$lang['profile_added_successfully']         = 'Mise à jour du profil réussie.';
$lang["invalid_country_code"]               = 'Veuillez saisir un identifiant de pays valide.';
$lang['bank_detail_change_error']           = 'Vos coordonnées bancaires ont déjà été vérifiées, vous ne pouvez donc pas modifier les coordonnées bancaires.';
$lang['bank_detail_added_success']          = 'Détails bancaires ajoutés avec succès.';
$lang['bank_detail_update_success']         = 'Détails bancaires mis à jour avec succès.';
$lang['eighteen_years_old']                 = 'Seulement 18 ans autorisés.';
$lang['user_name_already_exists']           = 'Nom d\'utilisateur existe déjà.';
$lang['username_available']                 = 'Nom d\'utilisateur disponible.';
$lang['phone_no_already_exists']            = 'Le numéro de téléphone existe déjà.';
$lang['email_already_exists_message']       = 'Cet identifiant e-mail est déjà enregistré chez nous. Veuillez changer pour continuer.';
$lang['bank_detail_deleted']                = 'La banque a bien été supprimée.';
$lang['service_disabled'] 					= 'Désolé, ce service est le service handicapés alternatif.';
$lang['invalid_bank_details'] 				= 'Désolé, les détails fournis BANCAIRES ne correspond pas avec les détails associés à ce numéro de compte.';
$lang['bank_reject_subject'] 				= 'Vos coordonnées bancaires a été rejetée';
$lang['service_currently_unavailable'] 		= "Désolé, ce service ne fonctionne pas actuellement, s'il vous plaît réessayer plus tard.";
$lang['fantasy_not_allowed_in_state'] 		= "Désolé, faire du sport de fantaisie n'est pas autorisé dans les Etats indiens où vous avez ce compte bancaire.";
$lang['avatar_error'] 						= 'Quelques problèmes dans la recherche avatars.';
$lang['image_upload_success'] 				= 'Image de profil mise à jour avec succès.';
$lang['image_upload_error'] 				= 'Un problème pour trouver des avatars.';
$lang['invalid_ifsc_code'] 					= 'Désolé, le code IFSC fourni est incorrect.';
$lang['invalid_account_number'] 			= 'Désolé, le numéro de compte fourni est incorrect.';
$lang['pending_request_bank_delete'] 		= 'Il y a une demande de retrait en attente, ne peut pas supprimer ces données bancaires maintenant.';
$lang['profile_name_mismatch'] 				= "Les détails de noms fournis ici ne correspond pas aux informations fournies dans le profil, S'il vous plaît modifier vos informations de profil ou utiliser les détails ici corrects.";
$lang['duplicate_bank_details'] 			= 'Les informations bancaires fournies existe déjà.';

$lang["finance"]["invalid_promo_code"]              = 'Code promotionnel non valide. veuillez entrer un code valide.';
$lang["finance"]["invalid_deal"]              = 'Offre non valide. veuillez sélectionner une offre valide.';
$lang["finance"]["invalid_deal_amount"]              = 'Montant du dépôt non valide pour l\'offre sélectionnée.';
$lang["finance"]["promo_code_amount_range_invalid"] = 'Ce code promo n\'est pas pour le montant entré.';
$lang["finance"]["first_deposit_already_used"]      = 'Ce code promo valable pour le premier dépôt.';
$lang["finance"]["allowed_limit_exceed"]            = 'Vous avez dépassé le nombre utilisé autorisé.';
$lang["finance"]["payment_status_update_error"]     = 'Problème lors de la mise à jour du statut de paiement';
$lang["finance"]["deposit_success_subject"]         = 'Votre dépôt est réussi, amusez-vous!';
$lang["finance"]["admin_deposit"]                   = 'Montant déposé par l\'administrateur';
$lang["finance"]["admin_deposit_bonus"]             = 'Bonus déposé par l\'administrateur';
$lang["finance"]["admin_deposit_winning"]           = 'Montant gagnant déposé par l\'administrateur';
$lang["finance"]["admin_deposit_points"]            = 'Points déposés par l\'administrateur';
$lang["finance"]["admin_withdrawal"]                = 'Retrait du montant par l\'administrateur';
$lang["finance"]["admin_withdrawal_bonus"]          = 'Retrait de bonus par l\'administrateur';
$lang["finance"]["entry_fee_for"]                   = 'Frais d\'entrée pour %s';
$lang["finance"]["entry_fee_for_contest"]           = 'Frais d\'inscription au concours';
$lang["finance"]["refund_entry_fee_contest"]        = 'Remboursement des frais pour le concours';
$lang["finance"]["won_contest_prize"]               = 'Prix du concours remporté';
$lang["finance"]["firend_refferal"]                 = 'Montant de l\'investissement de référence';
$lang["finance"]["bonus_expired"]                   = 'Bonus expiré';
$lang["finance"]["promocode"]                       = 'Par Promocode';
$lang["finance"]["amount_deposit"]                  = 'Montant déposé';
$lang["finance"]["amount_withdrawal"]               = 'Retrait du montant';
$lang["finance"]["bonus_on_deposit"]                = 'Bonus de crédit en dépôt';
$lang["finance"]["coin_deposit"]                    = 'Dépôt de pièces';
$lang["finance"]["total_tds_deducted"]              = 'Total TDS déduit';
$lang["finance"]["signup_bonus"]                    = 'Bonus d\'inscription';
$lang["finance"]["referral_bonus_friend_mobile_verified"]= 'Bonus de parrainage pour la vérification mobile.';
$lang["finance"]["referral_contest"]                = 'Bonus de participation au concours référé';
$lang["finance"]["redeemed_from_store"]             = 'Échangé depuis le magasin';
$lang["finance"]["bet_for_prediction"]              = 'Miser des pièces pour la prédiction';
$lang["finance"]["prediction_won"]                  = 'Prédiction gagnée';
$lang["finance"]["order_cancel"]                    = 'Annuler la commande (remboursement)';
$lang["finance"]["min_withdraw_value_error"]        = 'Le montant minimum à retirer est %s';
$lang["finance"]["max_withdraw_value_error"]        = 'montant maximum de retrait est %s';
$lang["finance"]["referral_bonus_subject"]          = 'Félicitations! Votre filleul a rejoint le '.SITE_TITLE;
$lang["finance"]["withdraw_request_subject"]        = 'Demande de retrait reçue';
$lang["finance"]["deposit_email_subject"]           = 'Montant déposé.';
$lang["finance"]["deposit_coin_subject"]           = 'Pièce déposée.';
$lang["finance"]["coin_redemption_reward_subject"]           = 'Redeemed Coins Reward unlocked.';

$lang["finance"]["prediction_joined_subject"]           = 'Prédiction jointe.';
$lang["finance"]["friend_with_benefits"]            = 'Ami avec des avantages!';
$lang["finance"]["insufficent_amount"]              = 'Montant insuffisant des gains!';
$lang["finance"]["deal_redeem_bonus_text"]              = 'bonus pour deal!';
$lang["finance"]["deal_redeem_cash_text"]              = 'réel pour affaire!';
$lang["finance"]["deal_redeem_coin_text"]              = 'pièces à traiter!';
$lang["finance"]["max_usage_limit_code"] 			= "Utilisation maximale pour cette limite de code promotionnelle dépassée";

$lang['reward_worth']             = 'vaut';
$lang['promocode_default_desc']             = 'Montant du code promotionnel reçu';
$lang['promocode_cash_desc']                = 'Encaisse Promocode reçue';
$lang['promocode_bonus_desc']               = 'Bonus Promocode reçu';
$lang['common_transaction_description']     = 'Montant déposé';
$lang['default_transaction_desc']           = '%s déposé pour %s';
$lang['default_bonus_text']                 = 'bonus cash';
$lang['default_real_text']                  = 'argent réel';
$lang['default_coin_text']                  = 'pièces de monnaie';
$lang['default_referral_text']              = 'Référence';
$lang['default_signup_text']                = 's\'inscrire';
$lang['referral_signup_text']               = 's\'inscrire par un ami';
$lang['default_join_contest']               = 'participer à un concours d\'argent';
$lang['referral_join_contest']              = 'participer à un concours de cash par un ami';
$lang["signup_bonus_cash_referred"]         = 'Argent bonus attribué pour l\'inscription';
$lang["signup_real_cash_referred"]          = 'Argent réel attribué pour l\'inscription';
$lang["signup_coin_referred"]               = 'Pièces attribuées pour l\'inscription';
$lang["signup_bonus_cash_referral"]         = 'Prime de parrainage en espèces accordée à l\'inscription par un ami';
$lang["signup_real_cash_referral"]          = 'Réel de l\'argent réel attribué à l\'inscription par un ami';
$lang["signup_coin_referral"]               = 'Pièces de référence attribuées à l\'inscription par un ami';
$lang["email_verify_bonus_cash_referred"]   = 'Bonus en espèces attribué pour la vérification des e-mails';
$lang["email_verify_real_cash_referred"]    = 'Argent réel attribué pour la vérification des e-mails';
$lang["email_verify_coin_referred"]         = 'Pièces bonus attribuées pour la vérification des e-mails';
$lang["email_verify_bonus_cash_referral"]   = 'Argent en prime attribué lors de la vérification de l\'e-mail par un ami';
$lang["email_verify_real_cash_referral"]    = 'Argent réel remis lors de la vérification par e-mail d\'un ami';
$lang["email_verify_coin_referral"]         = 'Pièces attribuées lors de la vérification de l\'e-mail par un ami';
$lang["first_deposit_bonus_cash_referred"]  = 'Bonus en espèces attribué pour le premier dépôt';
$lang["first_deposit_real_cash_referred"]   = 'Argent réel attribué pour le premier dépôt';
$lang["first_deposit_coin_referred"]        = 'Pièces attribuées pour le premier dépôt';
$lang["first_deposit_bonus_cash_referral"]  = 'Argent en prime attribué au premier dépôt par un ami';
$lang["first_deposit_real_cash_referral"]   = 'Argent réel attribué au premier dépôt par un ami';
$lang["first_deposit_coin_referral"]        = 'Pièces attribuées au premier dépôt par un ami';
$lang["user_id_required"]                   = 'Identifiant utilisateur requis.';
$lang["deposit_error"]                      = 'Une erreur s\'est produite lors du dépôt.';
$lang["phone_no_not_found"]                 = 'Numéro de téléphone introuvable.';
$lang["unable_to_fetch_token"]              = 'Impossible de récupérer le jeton d\'accès';
$lang["error_occured"]                      = 'Une erreur s\'est produite';
$lang["withdraw_range_error"]               = 'Le montant du retrait doit être supérieur à %s et inférieur à %s';
$lang["multiple_withdraw_error"]            = 'Demande précédente toujours en attente, veuillez attendre l\'approbation de l\'administrateur.';
$lang["update_bank_details"]                = 'Veuillez d\'abord mettre à jour vos coordonnées bancaires.';
$lang["insufficient_withdraw_amount"]       = 'Montant insuffisant à retirer!';
$lang["user_id_not_found"]                  = 'ID de l\'utilisateur non trouvé.';
$lang['general_payment_error']              = 'Veuillez vous assurer de fournir des informations correctes. Si le problème persiste, veuillez contacter l\'équipe d\'assistance.';
$lang['email_already_invited']              = 'Courriel déjà invité!';
$lang['user_invitation_subject']            = 'Vous avez été invité à rejoindre '.SITE_TITLE;
$lang['invite_send_success']                = 'Invitation envoyée avec succès.';
$lang['payment_success']                    = 'La transaction de paiement a été effectuée avec succès.';
$lang['payment_error']                      = 'L\'opération de paiement a été annulée.';
$lang['paypal_redirect_message']            = 'Veuillez vérifier votre solde pour la confirmation du dépôt, contactez l\'équipe d\'assistance s\'il n\'est pas crédité sur votre compte.';
$lang['enter_valid_password']               = 'Entrez un mot de passe valide.';
$lang['select_other_than_previous_password'] = 'Veuillez sélectionner un nouveau mot de passe autre que l\'ancien mot de passe.';
$lang['subject_welcome_email']              = 'Bienvenue à '.SITE_TITLE;
$lang['new_email_subject']                  = '['.SITE_TITLE.'] Vérifiez votre nouveau courriel';
$lang['dear']                               = 'chère';
$lang['email_paragraph1']                   = 'Bienvenue à '.SITE_TITLE.' nous sommes heureux de vous avoir comme nouveau joueur sur notre site Web.';
$lang['email_paragraph3']                   = 'Veuillez vérifier %s pour confirmer votre adresse e-mail et activer votre compte.';
$lang['email_paragraph5']                   = 'Bienvenue à '.SITE_TITLE.', nous vous souhaitons de bons jeux.';
$lang['cheers']                             = 'À votre santé';
$lang['team']                               = SITE_TITLE.' Équipe';
$lang['footer_text']                        = 'COURRIEL AUTOMATIQUE - VEUILLEZ NE PAS RÉPONDRE.';
$lang['sports_id']                          = 'sports_id';

$lang['email']                              = 'email';
$lang['install_date']                       = 'date d\'installation';
$lang['phone_code']                         = 'code de téléphone';
$lang['phone_no']                           = 'numéro de téléphone';
$lang['device_type']                        = 'type d\'appareil';
$lang['device_id']                          = 'Reference de l\'appareil';
$lang['facebook_id']                        = 'Facebook ID';
$lang['google_id']                          = 'identifiant google';
$lang['confirmation_code']                  = 'code de confirmation';
$lang['forgot_password_key']                = 'mot de passe oublié';
$lang['email_activation_key']               = 'clé d\'activation par e-mail';
$lang['session_key']                        = 'clé de session';
$lang['note']                               = 'Remarque';
$lang['user_id']                            = 'identifiant d\'utilisateur';
$lang['friend_id']                          = 'id ami';
$lang['source_type']                        = 'Type de Source';
$lang['affiliate_type']                     = 'type d\'affilié';
$lang['amount_type']                        = 'type de montant';
$lang['contest_id']                         = 'identifiant du concours';
$lang['collection_id']                      = 'identifiant de collection';
$lang['amount']                             = 'montant';
$lang['promo_code']                         = 'code promo';
$lang['reason']                             = 'raison';
$lang['bonus_code']                         = 'code bonus';
$lang['first_name']                         = 'Prénom';
$lang['last_name']                          = 'nom de famille';
$lang['dob']                                = 'date de naissance';
$lang['bank_name']                          = 'Nom de banque';
$lang['ac_number']                          = 'numéro de compte';
$lang['ifsc_code']                          = 'code ifsc';
$lang['bank_document']                      = 'document bancaire';
$lang['upi_id']                             = 'ID UPI';
$lang['username']                           = 'Nom d\'utilisateur';
$lang['otp']                                = 'OTP';
$lang['gender']                             = 'Le genre';
$lang['country']                            = 'pays';
$lang['state']                              = 'Etat';
$lang['city']                               = 'ville';
$lang['pin_code']                          = 'code PIN';


$lang["invalid_ext"] = "Type d'image non valide.";

$lang["bank_verification_bonus_referral"] = "Bonus pour la vérification bancaire";
$lang["bank_verification_real_referral"] = "Montant réel pour la vérification bancaire";
$lang["bank_verification_coin_referral"] = "Pièces pour vérification bancaire";
$lang["bank_verification_bonus_referred"] = "Bonus pour la vérification bancaire";
$lang["bank_verification_real_referred"] = "Montant réel pour la vérification bancaire";
$lang["bank_verification_coin_referred"] = "Pièces pour vérification bancaire";

$lang["bank_verification_bonus_wo_referral"] = "Bonus pour la vérification bancaire";
$lang["bank_verification_real_wo_referral"] = "Montant réel pour la vérification bancaire";
$lang["bank_verification_coin_wo_referral"] = "Pièces pour vérification bancaire";
$lang["bonus_on_deal"] = "Bonus pour Deal";
$lang["real_on_deal"] = "Montant réel de l'accord";
$lang["coin_on_deal"] = "Pièces pour Deal";

$lang["insufficent_coins"]              = 'Pièces insuffisantes!';
$lang["coins_claimed_succ_msg"]              = 'Pièces rachetées avec succès!';

$lang["daily_streak_coins"] = "Pièces journalières.";
$lang["bonus_received_for_coins_redeem"] = "Bonus reçu pour l'échange de pièces.";
$lang["real_received_for_coins_redeem"] = "Montant réel reçu pour l'échange de pièces.";
$lang["coin_deduct_on_coin_redeem"] = "déduction de pièce de monnaie sur les pièces de monnaie.";
$lang["coin_claim_succ_msg"] = "Vous avez obtenu #coins# pièces par enregistrement quotidien.";
$lang["coins_aleady_claimed_msg"] = 'Vous avez déjà réclamé pour aujourd\'hui.';

//feedback
$lang["user_feedback_added_success_msg"] = 'Vous répondez ajouté.';


$lang["deposit_success_subject"] = 'Votre dépôt est réussi, amusez-vous!';
$lang['account_blocked_by_wrong_otp']  = 'Votre compte a été bloqué pendant 24 heures en raison d\'une activité suspecte.';

$lang["referral_code_exist"] = "Ce code de parrainage existe déjà.";
$lang['bonus_cash']  = 'Bonus Cash';
$lang['real_cash']  = 'Argent réel';
$lang['gift_voucher']  = 'Bon cadeau';
$lang['not_a_redeem_type']  = 'Pas un type d\'échange valide';


$lang["spinwheel_cash_desc"]		= "Gagner de l'argent réel en faisant tourner la roue";
$lang["spinwheel_bonus_desc"]		= "Bonus gagné en faisant tourner la roue";
$lang["spinwheel_coin_desc"]		= "Pièces gagnées en faisant tourner la roue";
$lang["spinwheel_merchandise"]		= "Prix gagné en faisant tourner la roue";
//affiliate module
$lang['enter_valid_aff_code']   = 'La filiale vous avez choisi est pas encore actif.';
$lang['aff_req_success']        = 'Merci de votre intérêt, notre équipe vous contactera pour plus de détails.';
$lang['aff_req_error']          = 'Il y a un problème, veuillez contacter l \' administrateur. ';
$lang['aff_summary_success']    = 'obtenir le résumé des affiliés avec succès.';
$lang['aff_summary_error']      = 'problème pour obtenir le résumé, veuillez réessayer plus tard.';
$lang['aff_tr_success']         = 'obtenir les transactions d\'affiliation avec succès.';
$lang['aff_tr_error']           = 'problème lors de la récupération des transactions, veuillez réessayer plus tard.';

$lang["insufficent_amount"] = 'Montant insuffisant dans votre portefeuille!';
$lang["coin_success_message"] = "L'achat de pièces a réussi.";
$lang["coin_purchase_message"] = "Achat de paquet de pièces.";
$lang["coin_success_message"] = "L'achat de pièces a réussi.";
$lang["self_exclusion_success"] = "Self exclusion values set successfully.";
$lang["private_contest_host_commission"] = "Commission a accordé pour l'hébergement privé concours";

$lang['state_declaration_successfully'] = "Déclaration d'état mise à jour avec succès.";

$lang['purchase_success'] = "Pièces achetées avec succès.";
$lang['purchase_error'] = "Google est ce qui indique que nous avons une question de connexion au paiement.";
$lang['wrong_device'] = "mauvais appareil, essayez avec Android ou iOS";
$lang['pending_txn'] = "Votre transaction est en attente sera bientôt mise à jour.";
$lang['already_update'] = "Le statut de la transaction est déjà mis à jour.";

$lang['file_upload_error'] = "Désolé, il y a un problème avec le téléchargement de fichiers. Veuillez réessayer.";

//scratch & win 
$lang["scratchwin_cash_desc"]		= "Gagnez de l'argent réel à partir de zéro et gagnez";
$lang["scratchwin_bonus_desc"]		= "Bonus gagné à partir de zéro et gagnez";
$lang["scratchwin_coin_desc"]		= "Gagnez des pièces à partir de zéro et gagnez";
$lang["claimed_success"]            = "Votre montant à gratter est réclamé";
$lang["better_luck"]                = "Plus de chance la prochaine fois";
$lang["already_claimed"]            = "Vous avez déjà fait une demande pour ce concours.";
$lang["invalid_details"]            = "user_id ou id de concours non valide";
$lang["already_edited"]             ="Vous avez déjà modifié le code de parrainage";

$lang["disable_country_signup_error"] = "Vous ne pouvez pas vous inscrire avec le numéro de téléphone d'un autre comté";

if(INT_VERSION == 1) {
    $lang['pan_already_exists']                 = "La carte d'identité existe déjà.";
    $lang['err_fname_update_post_pan_verify']   = "Vous ne pouvez pas mettre à jour le prénom après vérification de la carte d'identité.";
    $lang['err_lname_update_post_pan_verify']   = "Vous ne pouvez pas mettre à jour le nom de famille après la vérification de la carte d'identité.";
    $lang['err_dob_update_post_pan_verify']     = "Vous ne pouvez pas mettre à jour le nom dob après la vérification de la carte d'identité.";
    $lang['err_update_post_pan_verify']         = "Vous ne pouvez pas mettre à jour les détails de la carte d'identité après la vérification de la carte d'identité.";
    $lang['pan_info_updated']                   = "Les détails de la carte d'identité ont été mis à jour avec succès.";
    $lang['duplicate_pan_no'] 					= "Désolé, cette carte d'identité existe déjà.";
    $lang['invalid_pan_no'] 					= "Désolé, cette carte d'identité est invalide.";
    $lang['invalid_pan_details'] 				= "Désolé, les détails de la carte d'identité fournis ne correspondent pas aux détails associés à ce numéro d'identification.";
    $lang['pan_card_reject_subject'] 			= "Votre carte d'identité a été rejetée";
    $lang['default_pan_vari']                   = "Vérification de la carte d'identité";
    $lang['referral_pan_vari']                  = "Carte d'identité vérifiée par un ami";
    $lang["pancard_bonus_cash_referred"]        = "Bonus en espèces attribué pour la vérification de la carte d'identité";
    $lang["pancard_real_cash_referred"]         = "Argent réel attribué pour la vérification de la carte d'identité";
    $lang["pancard_coin_referred"]              = "Pièces attribuées pour la vérification de la carte d'identité";
    $lang["pancard_bonus_cash_referral"]        = "Bonus en espèces attribué lors de la vérification de la carte d'identité par un ami";
    $lang["pancard_real_cash_referral"]         = "Argent réel attribué lors de la vérification de la carte d'identité par un ami";
    $lang["pancard_coin_referral"]              = "Pièces attribuées lors de la vérification de la carte d'identité par un ami";
    $lang["finance"]["referral_bonus_pan_verification"] = "Bonus de parrainage pour la vérification de la carte d'identité.";
    $lang['pan_no']                             = "Carte d'identité";
    $lang['pan_image']                          = "image de la carte d'identité";
} else {
    $lang['pan_already_exists']                 = 'Le numéro PAN existe déjà.';
    $lang['err_fname_update_post_pan_verify']   = 'Vous ne pouvez pas mettre à jour le prénom après la vérification PAN.';
    $lang['err_lname_update_post_pan_verify']   = 'Vous ne pouvez pas mettre à jour le nom de famille après vérification PAN.';
    $lang['err_dob_update_post_pan_verify']     = 'Vous ne pouvez pas mettre à jour le nom du dob après la vérification PAN.';
    $lang['err_update_post_pan_verify']         = 'Vous ne pouvez pas mettre à jour les détails de la carte PAN après vérification PAN.';
    $lang['pan_info_updated']                   = 'Détails PAN mis à jour avec succès.';
    $lang['duplicate_pan_no'] 					= 'Désolé, ce numéro PAN existe déjà.';
    $lang['invalid_pan_no'] 					= 'Désolé, ce numéro PAN est non valide.';
    $lang['invalid_pan_details'] 				= 'Désolé, les détails PAN fournies ne correspondent pas avec les détails associés à ce numéro PAN.';
    $lang['pan_card_reject_subject'] 			= 'Votre Pancard a été rejeté';
    $lang["finance"]["referral_bonus_pan_verification"] = 'Bonus de parrainage pour la vérification de la carte panoramique.';
    $lang['default_pan_vari']                   = 'vérification pancard';
    $lang['referral_pan_vari']                  = 'pancard vérifié par un ami';
    $lang["pancard_bonus_cash_referred"]        = 'Prime en espèces attribuée pour la vérification de la carte panoramique';
    $lang["pancard_real_cash_referred"]         = 'Real Cash décerné pour la vérification de la carte panoramique';
    $lang["pancard_coin_referred"]              = 'Pièces attribuées pour la vérification de la carte panoramique';
    $lang["pancard_bonus_cash_referral"]        = 'Argent bonus attribué lors de la vérification de la carte panoramique par un ami';
    $lang["pancard_real_cash_referral"]         = 'Argent réel attribué lors de la vérification de la carte panoramique par un ami';
    $lang["pancard_coin_referral"]              = 'Pièces attribuées lors de la vérification de la carte panoramique par un ami';
    $lang['pan_no']                             = 'Numéro PAN';
    $lang['pan_image']                          = 'Image PAN';
}


//subscription messages
$lang['subscription_module_enable']         = 'L\'abonnement ou le module de pièces n\'est pas activé, veuillez contacter l\'administrateur.';
$lang['no_subscription_package']            = "Aucun tel forfait d'abonnement n'existe.";
$lang['already_subscribed']                  = "Vous avez déjà souscrit à ce package.";
$lang['other_package_subscribed']            = "un autre package déjà abonné.";
$lang['validation_error']                   = "Erreur lors de la validation de la transaction.";
$lang['success_purchase']                   = "package souscrit avec succès";
$lang['package_canceled']                   = "package annulé avec succès";
$lang['package_canceled_error']             = "Impossible d'annuler l'abonnement, veuillez réessayer plus tard.";
$lang['get_packages']                       = "obtenir la liste des packages avec succès";
$lang['no_package']                         = "Aucun package disponible";

$lang['valid_value']                        = "S'il vous plaît fournir le %s valide";
$lang['quiz_already_claimed']               = "Vous avez déjà revendiqué vos récompenses pour le quiz d'aujourd'hui";

$lang['err_enter_valid_question_answer']	    ="Veuillez entrer une question valide avec la réponse.";
$lang['quiz_uid'] ="Quiz uid";
$lang['question_selected_options'] ="Questions des options sélectionnées.";

$lang['succ_quiz_claimed'] ="Quiz revendiqué avec succès.";
$lang['succ_claim_download_app_coin'] ="La pièce réclamée avec succès.";
$lang['err_already_coin_claimed'] ="Vous avez déjà revendiqué des pièces de monnaie pour l'application de téléchargement.";

$lang['err_download_app_claim']	    ="Connectez-vous en utilisant l'application pour réclamer les avantages.";

$lang["succ_spin_won"]                        ="Vous avez gagné une roue d'essorage";
$lang["err_spin_today_claimed"]                        ="Vous courez déjà une roue d'essorage aujourd'hui s'il vous plaît essayez demain";
$lang['withdraw_success']               = "Demande de retrait soumise avec succès";

//Crypto
$lang['crypto']['invalid_currency']             	= 'Devise invalide';
// $lang['crypto']['crypto_min_amount']            	= 'Le montant minimum pour {currency} est de {min_amount} USD';
$lang['crypto']['decimal_no_not_allowed']       	= 'Nombre décimal non autorisé';
$lang['crypto']['something_went_wrong'] 	    	= "Une erreur s'est produite. Veuillez réessayer";
$lang['crypto']['transaction_already_process'] 		= 'Transaction déjà traitée';
$lang['crypto']['transaction_updated_successfully'] = 'Transaction mise à jour avec succès';
$lang['crypto']['invalid_status'] 					= 'Statut invalide';
$lang['crypto']['invalid_transaction_id_or_client'] = 'Identifiant de transaction ou identifiant de transaction client non valide';
$lang['crypto']['pending_status']                   = 'Le statut de votre transaction sera bientôt mis à jour';
$lang['crypto']['crypto_wallet_change_error']       = 'Vos coordonnées déjà vérifiées, vous ne pouvez donc pas changer les détails du portefeuille.';
$lang['crypto_wallet_added_success']      = 'Crypto portefeuille ajouté avec succès.';
$lang['crypto']['duplicate_crypto_wallet_details'] 	= 'Les détails de la portefeuille fournis existe déjà.';
$lang['withdraw_success']               = "Demande de retrait soumise avec succès";
$lang['crypto']                             = 'ADRESSE CRYPTO';
$lang['bank']                             = 'Banque';
$lang['crypto_wallet']                     = 'portefeuille crpto';

//cashfree payout
$lang["finance"]["daily_withdraw_limit"]                = 'Vous êtes autorisé à retirer %s des transactions S en une journée.';
$lang["finance"]["cf_insufficient_bal"]                 = 'Impossible de continuer, veuillez essayer après un certain temps.';
$lang["finance"]["withdraw_request_received"]           = "Demande de retrait reçu";
$lang["finance"]["no_pout_active"]                      = "Aucun paiement n'est disponible, contact avec admin";

$lang['token_error'] 			= 'ne peut pas générer de jeton pour lintention upi, réessayez plus tard.';
$lang['withdraw_success_in_few_min']        = "Le statut de la transaction sera mis à jour dans quelques minutes.";

$lang['duplicate_aadhar_no'] = 'Sorry, this Aadhaar number already exist.';
$lang['err_update_post_aadhar_verify'] = 'You can not update Aadhaar details after Aadhaar verification.';
$lang['aadhar_detail_added_success'] = 'Aadhaar detail added successfully.';
$lang['aadhar_detail_update_success'] = 'Aadhaar detail updated successfully.';

// wdl 2fa
$lang['finance']['wdl_2fa_disable'] = "L'authentification de retrait 2fa est désactivée.";
$lang['finance']['provide_all_parameter'] = 'Veuillez fournir tous les paramètres.';
$lang['finance']['invalid_otp'] = 'Code OTP invalide. Veuillez entrer le bon OTP.';
$lang['finance']['otp_expired'] = 'OTP est expiré.';
$lang['aadhar_number'] = 'aadhar number';
$lang['invalid_aadhar_number'] = 'Numéro Aadhaar invalide, veuillez entrer un numéro aadhar valide';
$lang['request_id'] = 'Identifiant de la demande';
$lang['task_id'] = 'Identifiant de la tâche';
$lang['invalid_aadhar_number_otp'] = 'OTP invalide, veuillez saisir un OTP valide';

//Manual pg
$lang['manualpg']['invalid_bank_ref'] = "ID de référence bancaire non valide";
$lang['manualpg']['invalid_type_id'] = "Type de paiement invalide IS";
$lang['manualpg']['success_update'] = "Votre transaction est enregistrée, prochainement elle sera mise à jour.";

$lang['validate_gst_number'] = "Le numéro de TPS doit être composé de 15 chiffres";
$lang['gst_number'] = 'Numéro de TPS';