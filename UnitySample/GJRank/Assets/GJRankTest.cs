using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;

public class GJRankTest : MonoBehaviour {
    
    public Dropdown gameMode;

    public InputField gameId;
    public InputField gameName;

    public InputField nickname;
    public InputField score;

    public Text resultText;

    

    public void OnTestCreateGameID()
    {

        if(gameId.text == "" || gameName.text == "")
        {
            resultText.text = "게임아이디와 게임이름을 입력해야 합니다.";
            return;
        }
        
        if(gameMode.value == 1)
            GJRankSystem.Instance.getOrCreateGameID(gameId.text, gameName.text, GJRankSystem.SCORE_ORDER.REVERSE_ORDER, r => {
                resultText.text = r.message;
            });
        else
            GJRankSystem.Instance.getOrCreateGameID(gameId.text, gameName.text, r => {
                resultText.text = r.message;
            });
    }

    public void OnTestCreateNickname()
    {
        if (nickname.text == "")
        {
            resultText.text = "생성할 닉네임을 입력해야 합니다.";
            return;
        }

        GJRankSystem.Instance.getOrCreateNickname(nickname.text, r=> {
            resultText.text = r.message;
        });
    }

    public void OnTestPostScore()
    {
        if (nickname.text == "" || score.text == "")
        {
            resultText.text = "점수를 올릴 닉네임과 점수를 입력해야 합니다.";
            return;
        }

        GJRankSystem.Instance.postScore(nickname.text, int.Parse(score.text), r=> {
            resultText.text = r.message;
        });
    }
}
