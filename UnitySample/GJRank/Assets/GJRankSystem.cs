using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Networking;
using Newtonsoft.Json.Linq;


public class GJRankSystem : MonoBehaviour {
    

    string phpFile = "GameJamRank/GJRank.php";
    public string url = "http://127.0.0.1/";

    int myGameID = -1;

    const string command_getOrCreateGameID = "getOrCreateGameID";
    const string command_getOrCreateNickname = "getOrCreateNickname";
    const string command_postScore = "postScore";    


    public struct resultMessage
    {
        public bool result;
        public string message;
    }

    public enum SCORE_ORDER
    {
        NORMAL = 1,   //작은수 < 큰수
        REVERSE_ORDER = 2 // 큰수 < 작은수
    }

    protected static GJRankSystem _instance;
    public static GJRankSystem Instance
    {
        get
        {
            if (_instance == null)
            {
                _instance = FindObjectOfType<GJRankSystem>();
                if (_instance == null)
                {
                    GameObject obj = new GameObject();
                    _instance = obj.AddComponent<GJRankSystem>();
                }
            }
            return _instance;
        }
    }

    protected virtual void Awake()
    {
        DontDestroyOnLoad(this);
        if (_instance == null)
            _instance = this;
        else
            Destroy(gameObject);
    }


    JObject GetRequestJson(string command, JObject content)
    {
        JObject ret = new JObject();
        ret.Add("command", command);
        ret.Add("content", content);

        return ret;
    }


    // 최초 게임 시작시 호출!

    public void getOrCreateGameID(string gameID, string gameName, System.Action<resultMessage> result)
    {
        getOrCreateGameID(gameID, gameName, SCORE_ORDER.NORMAL, result);
    }

    resultMessage MakeResult(JObject res)
    {
        resultMessage message = new resultMessage();

        message.result = (res["result"].ToString() == "OK");
        if(res["content"]["responseValue"]["message"] != null)
            message.message = res["content"]["responseValue"]["message"].ToString();

        return message;
    }

    public void getOrCreateGameID(string gameID, string gameName, SCORE_ORDER order, System.Action<resultMessage> result)
    {
        JObject reqContent = new JObject();
        reqContent.Add("gameid", gameID);
        reqContent.Add("gamename", gameName);
        reqContent.Add("order", (int)order);

        StartCoroutine(PostData(GetRequestJson(command_getOrCreateGameID, reqContent), (o) => {
            myGameID = int.Parse(o["content"]["responseValue"]["id"].ToString());

            if (result != null)
                result(MakeResult(o));
        }));
    }


    //닉네임 생성시 호출!
    public bool getOrCreateNickname(string nickname, System.Action<resultMessage> result)
    {
        if (myGameID == -1)
            return false;


        JObject reqContent = new JObject();
        reqContent.Add("nickname", nickname);
        reqContent.Add("gameid", myGameID);

        StartCoroutine(PostData(GetRequestJson(command_getOrCreateNickname, reqContent), (o) => {
            if (result != null)
                result(MakeResult(o));
        }));
        return true;

    }

    //점수 올릴때 호출
    public void postScore(string nickname, int score, System.Action<resultMessage> result)
    {
        JObject reqContent = new JObject();
        reqContent.Add("nickname", nickname);
        reqContent.Add("gameid", myGameID);
        reqContent.Add("score", score);

        StartCoroutine(PostData(GetRequestJson(command_postScore, reqContent), o => {
            if (result != null)
                result(MakeResult(o));
        }));
        

    }

    IEnumerator PostData(JObject postData, System.Action<JObject> onComplete)
    {

        UnityWebRequest webRequest = UnityWebRequest.Put(url + phpFile, postData.ToString());
        webRequest.SetRequestHeader("Content-Type", "application/json");
        yield return webRequest.SendWebRequest();

        if(webRequest.isHttpError)
        {
            Debug.Log(webRequest.error);
        }
        else
        {
            if (onComplete != null)
                onComplete(JObject.Parse(webRequest.downloadHandler.text));
        }
    }
}
