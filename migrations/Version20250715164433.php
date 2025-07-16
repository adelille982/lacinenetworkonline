<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715164433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE announcement (id INT AUTO_INCREMENT NOT NULL, sub_category_announcement_id INT NOT NULL, user_id INT NOT NULL, type_announcement VARCHAR(255) NOT NULL, text_announcement LONGTEXT NOT NULL, department_announcement VARCHAR(255) NOT NULL, city_announcement VARCHAR(255) NOT NULL, availability_announcement DATE NOT NULL, expiry_announcement DATE NOT NULL, link_announcement VARCHAR(255) NOT NULL, remuneration TINYINT(1) NOT NULL, created_at_announcement DATETIME NOT NULL, INDEX IDX_4DB9D91CB8945000 (sub_category_announcement_id), INDEX IDX_4DB9D91CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE archived_event (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, back_to_image_id INT DEFAULT NULL, archived_at DATETIME NOT NULL, draft TINYINT(1) NOT NULL, INDEX IDX_E6F7038671F7E88B (event_id), UNIQUE INDEX UNIQ_E6F70386658D7DE7 (back_to_image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE archived_session_net_pitch_formation (id INT AUTO_INCREMENT NOT NULL, session_net_pitch_formation_id INT DEFAULT NULL, archived_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_BE8CA90EF0756863 (session_net_pitch_formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE back_to_image (id INT AUTO_INCREMENT NOT NULL, text_back_to_image LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_category (id INT AUTO_INCREMENT NOT NULL, slug_blog_category VARCHAR(255) NOT NULL, name_blog_category VARCHAR(255) NOT NULL, meta_description_blog_category VARCHAR(255) NOT NULL, seo_key_blog_category VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_category_blog_post (blog_category_id INT NOT NULL, blog_post_id INT NOT NULL, INDEX IDX_D9A2EB04CB76011C (blog_category_id), INDEX IDX_D9A2EB04A77FBEAF (blog_post_id), PRIMARY KEY(blog_category_id, blog_post_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, slug_blog_post VARCHAR(255) NOT NULL, title_post VARCHAR(255) NOT NULL, video_blog_post VARCHAR(255) DEFAULT NULL, main_img_post VARCHAR(255) NOT NULL, img_number2 VARCHAR(255) NOT NULL, img_number3 VARCHAR(255) DEFAULT NULL, img_number4 VARCHAR(255) DEFAULT NULL, short_description_blog_post LONGTEXT NOT NULL, content_blog_post LONGTEXT NOT NULL, content_blog_post2 LONGTEXT DEFAULT NULL, publication_date_blog_post DATE NOT NULL, author_blog_post VARCHAR(255) NOT NULL, meta_description_blog_post VARCHAR(255) NOT NULL, seo_key_blog_post VARCHAR(255) NOT NULL, draft TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_post_blog_post (blog_post_source INT NOT NULL, blog_post_target INT NOT NULL, INDEX IDX_5F433553B69807E8 (blog_post_source), INDEX IDX_5F433553AF7D5767 (blog_post_target), PRIMARY KEY(blog_post_source, blog_post_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_announcement (id INT AUTO_INCREMENT NOT NULL, img_category_announcement VARCHAR(255) NOT NULL, name_category_announcement VARCHAR(255) NOT NULL, color_category_announcement VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentary (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, archived_event_id INT DEFAULT NULL, net_pitch_formation_id INT DEFAULT NULL, text_commentary LONGTEXT NOT NULL, statut_commentary VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', validated_at DATETIME DEFAULT NULL, INDEX IDX_1CAC12CAA76ED395 (user_id), INDEX IDX_1CAC12CA8B0D9698 (archived_event_id), INDEX IDX_1CAC12CA683E6860 (net_pitch_formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, short_film_proposal TINYINT(1) NOT NULL, number_edition VARCHAR(255) NOT NULL, type_event VARCHAR(255) NOT NULL, title_event VARCHAR(255) NOT NULL, img_event VARCHAR(255) NOT NULL, date_event DATETIME NOT NULL, text_event LONGTEXT NOT NULL, program_event LONGTEXT NOT NULL, free TINYINT(1) NOT NULL, draft TINYINT(1) NOT NULL, price_event VARCHAR(255) DEFAULT NULL, INDEX IDX_3BAE0AA764D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_short_film (event_id INT NOT NULL, short_film_id INT NOT NULL, INDEX IDX_BA6DA99871F7E88B (event_id), INDEX IDX_BA6DA998FDDE862B (short_film_id), PRIMARY KEY(event_id, short_film_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_speaker (event_id INT NOT NULL, speaker_id INT NOT NULL, INDEX IDX_FED272CE71F7E88B (event_id), INDEX IDX_FED272CED04A0F27 (speaker_id), PRIMARY KEY(event_id, speaker_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gain (id INT AUTO_INCREMENT NOT NULL, img_gain VARCHAR(255) NOT NULL, title_gain VARCHAR(255) NOT NULL, slogan_gain VARCHAR(255) DEFAULT NULL, link_gain VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE general_cine_network (id INT AUTO_INCREMENT NOT NULL, email_company VARCHAR(255) NOT NULL, personal_email VARCHAR(255) NOT NULL, title_general_cine_network VARCHAR(255) NOT NULL, adresse_company VARCHAR(255) DEFAULT NULL, tel_company VARCHAR(255) DEFAULT NULL, text_about LONGTEXT NOT NULL, rgpd_content LONGTEXT DEFAULT NULL, img_form_announcement VARCHAR(255) NOT NULL, img_form_postulate VARCHAR(255) NOT NULL, img_form_net_pitch VARCHAR(255) NOT NULL, text_rgpd LONGTEXT DEFAULT NULL, meta_description_general_cine_network VARCHAR(255) NOT NULL, seo_key_general_cine_network VARCHAR(255) NOT NULL, img_comment_network VARCHAR(255) NOT NULL, img_comment_net_pitch VARCHAR(255) NOT NULL, img_form_login VARCHAR(255) DEFAULT NULL, img_form_registration VARCHAR(255) DEFAULT NULL, link_facebook VARCHAR(255) DEFAULT NULL, link_instagram VARCHAR(255) DEFAULT NULL, img_form_reset_pasword VARCHAR(255) DEFAULT NULL, short_film_proposal_home TINYINT(1) NOT NULL, replacement_image VARCHAR(255) NOT NULL, img_about VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE header (id INT AUTO_INCREMENT NOT NULL, page_type_header VARCHAR(255) NOT NULL, main_image_header VARCHAR(255) NOT NULL, title_header VARCHAR(255) NOT NULL, slogan_header VARCHAR(255) DEFAULT NULL, title_seo_page VARCHAR(255) DEFAULT NULL, meta_description_page VARCHAR(255) DEFAULT NULL, seo_key_page VARCHAR(255) DEFAULT NULL, draft TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE header_home (id INT AUTO_INCREMENT NOT NULL, video_header_home VARCHAR(255) NOT NULL, title_header_home VARCHAR(255) NOT NULL, slogan_header_home VARCHAR(255) NOT NULL, number_edition NUMERIC(10, 0) NOT NULL, number_participant NUMERIC(10, 0) NOT NULL, number_movie_received NUMERIC(10, 0) NOT NULL, number_movie_projected NUMERIC(10, 0) NOT NULL, draft TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE header_home_img (id INT AUTO_INCREMENT NOT NULL, header_home_id INT DEFAULT NULL, image_header_home VARCHAR(255) NOT NULL, INDEX IDX_985AB3E2FE2596A7 (header_home_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image_back_to_image (id INT AUTO_INCREMENT NOT NULL, back_to_image_id INT DEFAULT NULL, img_back_to_image VARCHAR(255) NOT NULL, INDEX IDX_70DB80E4658D7DE7 (back_to_image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, type_location VARCHAR(255) NOT NULL, street_location VARCHAR(255) NOT NULL, postal_code VARCHAR(255) NOT NULL, city_location VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location_speaker (location_id INT NOT NULL, speaker_id INT NOT NULL, INDEX IDX_95C0C8C564D218E (location_id), INDEX IDX_95C0C8C5D04A0F27 (speaker_id), PRIMARY KEY(location_id, speaker_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE net_pitch_formation (id INT AUTO_INCREMENT NOT NULL, gain_id INT DEFAULT NULL, slug_net_pitchformation VARCHAR(255) NOT NULL, title_net_pitch_formation VARCHAR(255) NOT NULL, max_number_net_pitch_formation NUMERIC(10, 0) NOT NULL, duration_net_pitch_formation VARCHAR(255) NOT NULL, funding_net_pitch_formation VARCHAR(255) NOT NULL, short_description_net_pitch_formation LONGTEXT NOT NULL, long_description_net_pitch_formation LONGTEXT NOT NULL, pdf_net_pitch_formation VARCHAR(255) DEFAULT NULL, program_description LONGTEXT DEFAULT NULL, meta_description_net_pitch_formation VARCHAR(255) NOT NULL, seo_key_net_pitch_formation VARCHAR(255) NOT NULL, draft TINYINT(1) NOT NULL, INDEX IDX_72D03716C60EF8C4 (gain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner (id INT AUTO_INCREMENT NOT NULL, name_partner VARCHAR(255) NOT NULL, logo_partner VARCHAR(255) NOT NULL, link_partner VARCHAR(2000) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE postulate (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, professional_experience LONGTEXT NOT NULL, curiculum VARCHAR(255) NOT NULL, condition_validated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_net_pitch_formation (id INT AUTO_INCREMENT NOT NULL, session_net_pitch_formation_id INT NOT NULL, archived_session_net_pitch_formation_id INT DEFAULT NULL, firstname_registration VARCHAR(255) NOT NULL, lastname_registration VARCHAR(255) NOT NULL, email_registration VARCHAR(255) NOT NULL, tel_registration VARCHAR(255) NOT NULL, afdas TINYINT(1) NOT NULL, professional_project_registration LONGTEXT NOT NULL, cv_registration VARCHAR(255) NOT NULL, statut_registration VARCHAR(255) NOT NULL, created_at_registration DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', condition_validated DATETIME NOT NULL, INDEX IDX_2BEBBA29F0756863 (session_net_pitch_formation_id), INDEX IDX_2BEBBA29F714202F (archived_session_net_pitch_formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session_net_pitch_formation (id INT AUTO_INCREMENT NOT NULL, net_pitch_formation_id INT DEFAULT NULL, location_id INT DEFAULT NULL, img_session_net_pitch_formation VARCHAR(255) NOT NULL, max_number_registration_session_net_pitch_formation NUMERIC(10, 0) NOT NULL, remote_session TINYINT(1) NOT NULL, start_date_session_net_pitch_formation DATETIME NOT NULL, end_date_session_net_pitch_formation DATETIME NOT NULL, draft TINYINT(1) NOT NULL, INDEX IDX_45E1AC6F683E6860 (net_pitch_formation_id), INDEX IDX_45E1AC6F64D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE short_film (id INT AUTO_INCREMENT NOT NULL, statut_short_film VARCHAR(255) NOT NULL, poster_short_film VARCHAR(255) NOT NULL, poster_pop_up_short_film VARCHAR(255) DEFAULT NULL, duration_short_film VARCHAR(255) DEFAULT NULL, genre_short_film VARCHAR(255) DEFAULT NULL, title_short_film VARCHAR(255) NOT NULL, production_short_film VARCHAR(255) DEFAULT NULL, pitch_short_film LONGTEXT DEFAULT NULL, img_proposal1 VARCHAR(255) DEFAULT NULL, img_proposal2 VARCHAR(255) DEFAULT NULL, img_proposal3 VARCHAR(255) DEFAULT NULL, img_proposal4 VARCHAR(255) DEFAULT NULL, img_proposal5 VARCHAR(255) DEFAULT NULL, link_short_film_proposal VARCHAR(255) DEFAULT NULL, link_trailer_short_film_proposal VARCHAR(255) DEFAULT NULL, format_dcp TINYINT(1) DEFAULT NULL, draft TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE short_film_speaker (short_film_id INT NOT NULL, speaker_id INT NOT NULL, INDEX IDX_F5A6C740FDDE862B (short_film_id), INDEX IDX_F5A6C740D04A0F27 (speaker_id), PRIMARY KEY(short_film_id, speaker_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE speaker (id INT AUTO_INCREMENT NOT NULL, email_speaker VARCHAR(255) DEFAULT NULL, tel_speaker VARCHAR(255) DEFAULT NULL, statut_speaker VARCHAR(255) NOT NULL, type_speaker VARCHAR(255) NOT NULL, role_speaker VARCHAR(255) NOT NULL, picture_speaker VARCHAR(255) NOT NULL, img_pop_up_speaker VARCHAR(255) DEFAULT NULL, first_name_speaker VARCHAR(255) NOT NULL, last_name_speaker VARCHAR(255) NOT NULL, biography_speaker LONGTEXT NOT NULL, picture_company_speaker VARCHAR(255) DEFAULT NULL, search LONGTEXT DEFAULT NULL, news_speaker LONGTEXT DEFAULT NULL, instagram_speaker VARCHAR(255) DEFAULT NULL, facebook_speaker VARCHAR(255) DEFAULT NULL, img_speaker_proposal1 VARCHAR(255) DEFAULT NULL, img_speaker_proposal2 VARCHAR(255) DEFAULT NULL, img_speaker_proposal3 VARCHAR(255) DEFAULT NULL, draft TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE speaker_session_net_pitch_formation (speaker_id INT NOT NULL, session_net_pitch_formation_id INT NOT NULL, INDEX IDX_3C519960D04A0F27 (speaker_id), INDEX IDX_3C519960F0756863 (session_net_pitch_formation_id), PRIMARY KEY(speaker_id, session_net_pitch_formation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_category_announcement (id INT AUTO_INCREMENT NOT NULL, category_announcement_id INT NOT NULL, name_sub_category VARCHAR(255) NOT NULL, INDEX IDX_4384429FC9B3B645 (category_announcement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, roles JSON NOT NULL, picture_user VARCHAR(255) DEFAULT NULL, firstname_user VARCHAR(255) NOT NULL, lastname_user VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, telephone_user VARCHAR(255) DEFAULT NULL, field_of_evolution_user LONGTEXT DEFAULT NULL, intermittent_user TINYINT(1) NOT NULL, curriculum_user VARCHAR(255) DEFAULT NULL, created_at_user DATETIME NOT NULL, is_verified TINYINT(1) NOT NULL, condition_validated DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_event (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, event_id INT NOT NULL, registered_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D96CF1FFA76ED395 (user_id), INDEX IDX_D96CF1FF71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91CB8945000 FOREIGN KEY (sub_category_announcement_id) REFERENCES sub_category_announcement (id)');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE archived_event ADD CONSTRAINT FK_E6F7038671F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE archived_event ADD CONSTRAINT FK_E6F70386658D7DE7 FOREIGN KEY (back_to_image_id) REFERENCES back_to_image (id)');
        $this->addSql('ALTER TABLE archived_session_net_pitch_formation ADD CONSTRAINT FK_BE8CA90EF0756863 FOREIGN KEY (session_net_pitch_formation_id) REFERENCES session_net_pitch_formation (id)');
        $this->addSql('ALTER TABLE blog_category_blog_post ADD CONSTRAINT FK_D9A2EB04CB76011C FOREIGN KEY (blog_category_id) REFERENCES blog_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_category_blog_post ADD CONSTRAINT FK_D9A2EB04A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_post_blog_post ADD CONSTRAINT FK_5F433553B69807E8 FOREIGN KEY (blog_post_source) REFERENCES blog_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_post_blog_post ADD CONSTRAINT FK_5F433553AF7D5767 FOREIGN KEY (blog_post_target) REFERENCES blog_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT FK_1CAC12CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT FK_1CAC12CA8B0D9698 FOREIGN KEY (archived_event_id) REFERENCES archived_event (id)');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT FK_1CAC12CA683E6860 FOREIGN KEY (net_pitch_formation_id) REFERENCES net_pitch_formation (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE event_short_film ADD CONSTRAINT FK_BA6DA99871F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_short_film ADD CONSTRAINT FK_BA6DA998FDDE862B FOREIGN KEY (short_film_id) REFERENCES short_film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_speaker ADD CONSTRAINT FK_FED272CE71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_speaker ADD CONSTRAINT FK_FED272CED04A0F27 FOREIGN KEY (speaker_id) REFERENCES speaker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE header_home_img ADD CONSTRAINT FK_985AB3E2FE2596A7 FOREIGN KEY (header_home_id) REFERENCES header_home (id)');
        $this->addSql('ALTER TABLE image_back_to_image ADD CONSTRAINT FK_70DB80E4658D7DE7 FOREIGN KEY (back_to_image_id) REFERENCES back_to_image (id)');
        $this->addSql('ALTER TABLE location_speaker ADD CONSTRAINT FK_95C0C8C564D218E FOREIGN KEY (location_id) REFERENCES location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE location_speaker ADD CONSTRAINT FK_95C0C8C5D04A0F27 FOREIGN KEY (speaker_id) REFERENCES speaker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE net_pitch_formation ADD CONSTRAINT FK_72D03716C60EF8C4 FOREIGN KEY (gain_id) REFERENCES gain (id)');
        $this->addSql('ALTER TABLE registration_net_pitch_formation ADD CONSTRAINT FK_2BEBBA29F0756863 FOREIGN KEY (session_net_pitch_formation_id) REFERENCES session_net_pitch_formation (id)');
        $this->addSql('ALTER TABLE registration_net_pitch_formation ADD CONSTRAINT FK_2BEBBA29F714202F FOREIGN KEY (archived_session_net_pitch_formation_id) REFERENCES archived_session_net_pitch_formation (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE session_net_pitch_formation ADD CONSTRAINT FK_45E1AC6F683E6860 FOREIGN KEY (net_pitch_formation_id) REFERENCES net_pitch_formation (id)');
        $this->addSql('ALTER TABLE session_net_pitch_formation ADD CONSTRAINT FK_45E1AC6F64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE short_film_speaker ADD CONSTRAINT FK_F5A6C740FDDE862B FOREIGN KEY (short_film_id) REFERENCES short_film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE short_film_speaker ADD CONSTRAINT FK_F5A6C740D04A0F27 FOREIGN KEY (speaker_id) REFERENCES speaker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE speaker_session_net_pitch_formation ADD CONSTRAINT FK_3C519960D04A0F27 FOREIGN KEY (speaker_id) REFERENCES speaker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE speaker_session_net_pitch_formation ADD CONSTRAINT FK_3C519960F0756863 FOREIGN KEY (session_net_pitch_formation_id) REFERENCES session_net_pitch_formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sub_category_announcement ADD CONSTRAINT FK_4384429FC9B3B645 FOREIGN KEY (category_announcement_id) REFERENCES category_announcement (id)');
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FF71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE announcement DROP FOREIGN KEY FK_4DB9D91CB8945000');
        $this->addSql('ALTER TABLE announcement DROP FOREIGN KEY FK_4DB9D91CA76ED395');
        $this->addSql('ALTER TABLE archived_event DROP FOREIGN KEY FK_E6F7038671F7E88B');
        $this->addSql('ALTER TABLE archived_event DROP FOREIGN KEY FK_E6F70386658D7DE7');
        $this->addSql('ALTER TABLE archived_session_net_pitch_formation DROP FOREIGN KEY FK_BE8CA90EF0756863');
        $this->addSql('ALTER TABLE blog_category_blog_post DROP FOREIGN KEY FK_D9A2EB04CB76011C');
        $this->addSql('ALTER TABLE blog_category_blog_post DROP FOREIGN KEY FK_D9A2EB04A77FBEAF');
        $this->addSql('ALTER TABLE blog_post_blog_post DROP FOREIGN KEY FK_5F433553B69807E8');
        $this->addSql('ALTER TABLE blog_post_blog_post DROP FOREIGN KEY FK_5F433553AF7D5767');
        $this->addSql('ALTER TABLE commentary DROP FOREIGN KEY FK_1CAC12CAA76ED395');
        $this->addSql('ALTER TABLE commentary DROP FOREIGN KEY FK_1CAC12CA8B0D9698');
        $this->addSql('ALTER TABLE commentary DROP FOREIGN KEY FK_1CAC12CA683E6860');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA764D218E');
        $this->addSql('ALTER TABLE event_short_film DROP FOREIGN KEY FK_BA6DA99871F7E88B');
        $this->addSql('ALTER TABLE event_short_film DROP FOREIGN KEY FK_BA6DA998FDDE862B');
        $this->addSql('ALTER TABLE event_speaker DROP FOREIGN KEY FK_FED272CE71F7E88B');
        $this->addSql('ALTER TABLE event_speaker DROP FOREIGN KEY FK_FED272CED04A0F27');
        $this->addSql('ALTER TABLE header_home_img DROP FOREIGN KEY FK_985AB3E2FE2596A7');
        $this->addSql('ALTER TABLE image_back_to_image DROP FOREIGN KEY FK_70DB80E4658D7DE7');
        $this->addSql('ALTER TABLE location_speaker DROP FOREIGN KEY FK_95C0C8C564D218E');
        $this->addSql('ALTER TABLE location_speaker DROP FOREIGN KEY FK_95C0C8C5D04A0F27');
        $this->addSql('ALTER TABLE net_pitch_formation DROP FOREIGN KEY FK_72D03716C60EF8C4');
        $this->addSql('ALTER TABLE registration_net_pitch_formation DROP FOREIGN KEY FK_2BEBBA29F0756863');
        $this->addSql('ALTER TABLE registration_net_pitch_formation DROP FOREIGN KEY FK_2BEBBA29F714202F');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE session_net_pitch_formation DROP FOREIGN KEY FK_45E1AC6F683E6860');
        $this->addSql('ALTER TABLE session_net_pitch_formation DROP FOREIGN KEY FK_45E1AC6F64D218E');
        $this->addSql('ALTER TABLE short_film_speaker DROP FOREIGN KEY FK_F5A6C740FDDE862B');
        $this->addSql('ALTER TABLE short_film_speaker DROP FOREIGN KEY FK_F5A6C740D04A0F27');
        $this->addSql('ALTER TABLE speaker_session_net_pitch_formation DROP FOREIGN KEY FK_3C519960D04A0F27');
        $this->addSql('ALTER TABLE speaker_session_net_pitch_formation DROP FOREIGN KEY FK_3C519960F0756863');
        $this->addSql('ALTER TABLE sub_category_announcement DROP FOREIGN KEY FK_4384429FC9B3B645');
        $this->addSql('ALTER TABLE user_event DROP FOREIGN KEY FK_D96CF1FFA76ED395');
        $this->addSql('ALTER TABLE user_event DROP FOREIGN KEY FK_D96CF1FF71F7E88B');
        $this->addSql('DROP TABLE announcement');
        $this->addSql('DROP TABLE archived_event');
        $this->addSql('DROP TABLE archived_session_net_pitch_formation');
        $this->addSql('DROP TABLE back_to_image');
        $this->addSql('DROP TABLE blog_category');
        $this->addSql('DROP TABLE blog_category_blog_post');
        $this->addSql('DROP TABLE blog_post');
        $this->addSql('DROP TABLE blog_post_blog_post');
        $this->addSql('DROP TABLE category_announcement');
        $this->addSql('DROP TABLE commentary');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_short_film');
        $this->addSql('DROP TABLE event_speaker');
        $this->addSql('DROP TABLE gain');
        $this->addSql('DROP TABLE general_cine_network');
        $this->addSql('DROP TABLE header');
        $this->addSql('DROP TABLE header_home');
        $this->addSql('DROP TABLE header_home_img');
        $this->addSql('DROP TABLE image_back_to_image');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE location_speaker');
        $this->addSql('DROP TABLE net_pitch_formation');
        $this->addSql('DROP TABLE partner');
        $this->addSql('DROP TABLE postulate');
        $this->addSql('DROP TABLE registration_net_pitch_formation');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE session_net_pitch_formation');
        $this->addSql('DROP TABLE short_film');
        $this->addSql('DROP TABLE short_film_speaker');
        $this->addSql('DROP TABLE speaker');
        $this->addSql('DROP TABLE speaker_session_net_pitch_formation');
        $this->addSql('DROP TABLE sub_category_announcement');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_event');
    }
}
