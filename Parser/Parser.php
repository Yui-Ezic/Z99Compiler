<?php


namespace Z99Parser;


use Z99Compiler\Entity\Tree\Node;
use Z99Parser\Exceptions\ParserException;
use Z99Parser\Streams\TokenStreamInterface;

class Parser
{
    /**
     * @var TokenStreamInterface
     */
    private $stream;

    public function __construct(TokenStreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function program(): Node
    {
        $root = new Node('program');
        $root->addChild($this->matchOrFail('Program'));
        $root->addChild($this->matchOrFail('Ident'));
        $root->addChild($this->matchOrFail('Var'));
        $root->addChild($this->declareList());
        $root->addChild($this->matchOrFail('Semi'));
        $root->addChild($this->matchOrFail('Begin'));
        $root->addChild($this->statementList());
        $root->addChild($this->matchOrFail('Semi'));
        $root->addChild($this->matchOrFail('End'));
        $root->addChild($this->matchOrFail('EOF'));

        return $root;
    }

    private function match($lexeme): ?Node
    {
        if ($this->stream->lookAhead()->getType() === $lexeme) {
            $root = new Node($lexeme);
            $root->addChild(new Node($this->stream->next()->getString()));
            return $root;
        }

        return null;
    }

    private function matchOrFail($lexeme): Node
    {
        if ($result = $this->match($lexeme)) {
            return $result;
        }

        throw new ParserException("Expected $lexeme", $this->stream->lookAhead());
    }

    private function matchOneOfLexeme(array $lexemes): Node
    {
        foreach ($lexemes as $lexeme) {
            if ($result = $this->match($lexeme)) {
                return $result;
            }
        }

        throw new ParserException('Expected one of this lexemes ' . implode(', ', $lexemes), $this->stream->lookAhead());
    }

    private function matchOneOfRules(array $rules): Node
    {
        foreach ($rules as $rule) {
            if ($result = $this->matchRule($rule)) {
                return $result;
            }
        }

        throw new ParserException('Expected one of this rules ' . implode(', ', $rules), $this->stream->lookAhead());
    }

    private function matchRule(string $rule): ?Node
    {
        $position = $this->stream->remember();
        try {
            return $this->$rule();
        } catch (ParserException $e) {
            $this->stream->goTo($position);
        }

        return null;
    }

    public function declareList(): Node
    {
        $root = new Node('declareList');
        $root->addChild($this->declaration());

        $this->repeatedMatch(function () use ($root) {
            $tokens = [$this->matchOrFail('Semi'), $this->declaration()];
            $root->addChild($tokens[0]);
            $root->addChild($tokens[1]);
        });

        return $root;
    }

    public function declaration(): Node
    {
        $root = new Node('declaration');
        $root->addChild($this->identList());
        $root->addChild($this->matchOrFail('Colon'));
        $root->addChild($this->matchOrFail('Type'));

        return $root;
    }

    public function identList(): Node
    {
        $root = new Node('identList');
        $root->addChild($this->matchOrFail('Ident'));

        $this->repeatedMatch(function () use ($root) {
            $tokens = [$this->matchOrFail('Comma'), $this->matchOrFail('Ident')];
            $root->addChild($tokens[0]);
            $root->addChild($tokens[1]);
        });

        return $root;
    }

    private function repeatedMatch(callable $function): void
    {
        while (true) {
            $position = $this->stream->remember();
            try {
                $function();
            } catch (ParserException $e) {
                $this->stream->goTo($position);
                break;
            }
        }
    }

    public function statementList(): Node
    {
        $root = new Node('statementList');
        $root->addChild($this->statement());

        $this->repeatedMatch(function () use ($root) {
            $tokens = [$this->matchOrFail('Semi'), $this->statement()];
            $root->addChild($tokens[0]);
            $root->addChild($tokens[1]);
        });

        return $root;
    }

    public function statement(): Node
    {
        $root = new Node('statement');
        $root->addChild($this->matchOneOfRules(['assign', 'input', 'output', 'branchStatement', 'repeatStatement']));
        return $root;
    }

    public function input(): Node
    {
        $root = new Node('input');
        $root->addChild($this->matchOrFail('Read'));
        $root->addChild($this->matchOrFail('LBracket'));
        $root->addChild($this->identList());
        $root->addChild($this->matchOrFail('RBracket'));
        return $root;
    }

    public function output(): Node
    {
        $root = new Node('output');
        $root->addChild($this->matchOrFail('Write'));
        $root->addChild($this->matchOrFail('LBracket'));
        $root->addChild($this->identList());
        $root->addChild($this->matchOrFail('RBracket'));
        return $root;
    }

    public function branchStatement(): Node
    {
        $root = new Node('branchStatement');
        $root->addChild($this->matchOrFail('If'));
        $root->addChild($this->expression());
        $root->addChild($this->matchOrFail('Then'));
        $root->addChild($this->statementList());
        $root->addChild($this->matchOrFail('Semi'));
        $root->addChild($this->matchOrFail('Fi'));
        return $root;
    }

    public function repeatStatement(): Node
    {
        $root = new Node('repeatStatement');
        $root->addChild($this->matchOrFail('Repeat'));
        $root->addChild($this->statementList());
        $root->addChild($this->matchOrFail('Semi'));
        $root->addChild($this->matchOrFail('Until'));
        $root->addChild($this->boolExpr());
        return $root;
    }

    public function assign(): Node
    {
        $root = new Node('assign');
        $root->addChild($this->matchOrFail('Ident'));
        $root->addChild($this->matchOrFail('AssignOp'));
        $root->addChild($this->expression());
        return $root;
    }

    public function expression(): Node
    {
        $root = new Node('expression');
        $root->addChild($this->matchOneOfRules(['boolExpr', 'arithmExpression']));
        return $root;
    }

    public function arithmExpression(): Node
    {
        $root = new Node('arithmExpression');
        $children = [];
        $position = $this->stream->remember();
        try {
            $children[] = $this->term();
            $children[] = $this->addOp();
            $children[] = $this->arithmExpression();
        } catch (ParserException $e ) {
            $this->stream->goTo($position);
            $children = [$this->term()];
        }
        $root->addChildren($children);

        return $root;
    }

    public function boolExpr(): Node
    {
        $root = new Node('boolExpr');
        $root->addChild($this->arithmExpression());
        $root->addChild($this->matchOrFail('RelOp'));
        $root->addChild($this->arithmExpression());
        return $root;
    }

    public function term(): Node
    {
        $root = new Node('term');
        $children = [];
        $position = $this->stream->remember();
        try {
            $children[] = $this->factor();
            $children[] = $this->multOp();
            $children[] = $this->term();
        } catch (ParserException $e) {
            $this->stream->goTo($position);
            $children = [$this->factor()];
        }
        $root->addChildren($children);

        return $root;
    }

    public function factor(): Node
    {
        $root = new Node('factor');
        if ($match = $this->match('Ident')) {
            $root->addChild($match);
            return $root;
        }

        if ($match = $this->matchRule('constant')) {
            $root->addChild($match);
            return $root;
        }

        try {
            $root->addChild($this->matchOrFail('LBracket'));
            $root->addChild($this->arithmExpression());
            $root->addChild($this->matchOrFail('RBracket'));
        } catch (ParserException $e) {
            throw new ParserException('Expected Ident, constant or (arithmExpression)', $this->stream->lookAhead());
        }

        return $root;
    }

    public function addOp(): Node
    {
        $root = new Node('addOp');
        $root->addChild($this->matchOneOfLexeme(['Plus', 'Minus']));
        return $root;
    }

    public function multOp(): Node
    {
        $root = new Node('multOp');
        $root->addChild($this->matchOneOfLexeme(['Star', 'Slash']));
        return $root;
    }

    public function constant(): Node
    {
        $root = new Node('constant');
        $root->addChild($this->matchOneOfLexeme(['IntNum', 'RealNum', 'BoolConst']));
        return $root;
    }
}