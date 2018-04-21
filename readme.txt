[DB정보]
GameJamRank.sql 적용
GJRankDB.php 에 설정

[랭킹보기]
예) http://127.0.0.1/GameJamRank/GJScore.php?gameid=testGame 로 랭킹 볼수 있음.

[유니티]
1. 유니티에서 Json.Net for unity Asset 임포트

2. 최초 씬(보통 타이틀씬) 에서 GJRankSystem 프리팹 씬으로 가져오기

3. 최초 씬 매니저 스크립트에서 아래와 같이 호출 보통 Start() 에서 하면 됨. 

            GJRankSystem.Instance.getOrCreateGameID(게임아이디, 게임이름, r => {
                resultText.text = r.message;    
            });

            => 게임아이디가 이미 존재할경우 메시지로 알수 있음. 
            예) 게임아이디 : testGame, 게임이름 : 테스트게임

작은수가 랭킹이 더 높을때는 GJRankSystem.SCORE_ORDER.REVERSE_ORDER 옵션추가

            GJRankSystem.Instance.getOrCreateGameID(gameId.text, gameName.text, GJRankSystem.SCORE_ORDER.REVERSE_ORDER, r => {
                resultText.text = r.message;
            });


4. 닉네임 서버에 등록시

        GJRankSystem.Instance.getOrCreateNickname(nickname.text, r=> {
            resultText.text = r.message;
        });

        호출

5. 점수 올릴때

        GJRankSystem.Instance.postScore(nickname.text, int.Parse(score.text), r=> {
            resultText.text = r.message;
        });

        호출

