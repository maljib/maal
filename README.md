# maal
# Mac 개발환경 설치
1. https://sourceforge.net/projects/xampp/files/XAMPP%20Mac%20OS%20X/5.6.40/ 에서 ...installer.dmg 파일을 찾아 내려받아 설치한다.
2. ~/Applications/XAMPP/xamppfiles/htdocs 폴더에서 다음 명령을 실행한다:
composer require phpmailer/phpmailer
git clone git@github.com:maljib/maal.git
mkdir maal/p
chmod 777 maal/p
3. XAMPP - Manage Servers - MySQL Database - Configure - Open Conf File - Yes
4. my.cnf 파일 "myisam_sort_buffer_size = 8M" 줄 밑에 다음 2줄을 추가하고 저장한다:
character-set-server = utf8
skip-character-set-client-handshake
5. XAMPP - Manage Servers - Start All
6. 크롬에서 http://localhost/phpmyadmin
7. 사용자 계정 - 사용자 추가 
  - 로그인 정보: 사용자명(scott), 호스트명(localhost), 암호(...), 재입력(...)
  - 전체적 권한 [v] 모두 체크
  - [실행]
8. 가져오기 - 파일 선택(wordlist_yyyymmdd.sql) - [실행]
9. phpMyAdmin - wordlist - 테이블 작업 -
  - 데이터 정렬 방식(utf8mb4_unicode_ci)
  - [v] Change all tables collations
  - [실행]
10. 크롬에서 http://localhost/maal/ -- 테스트.
